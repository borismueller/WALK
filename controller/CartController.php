<?php

require_once '../repository/ProductRepository.php';
require_once '../repository/TypeRepository.php';
require_once '../repository/CartRepository.php';
require_once '../repository/UserRepository.php';

/**
* Siehe Dokumentation im DefaultController.
*/
class CartController
{
  public function index() {
      $productRepository = new ProductRepository();
      $typeRepository = new TypeRepository();
			$view = new View('cart_index');
			$view->title = 'Cart';

      if (!empty($_SESSION['cart']['products'])) {
        $cart = $_SESSION['cart'];
        $productIds = $cart['products'];

        $products = array();

        foreach ($productIds as $id) {
          $products[] = $productRepository->readById($id);
        }

				$fullPrice = 0;
        foreach ($products as $key => $product) {
					if (empty($product)){
						//remove element if it's null, can happen with a weird id
						unset($products[$key]);
					} else {
	          //add the name of the type to the array & and add up price
	          $product->type = $typeRepository->readById($product->type_id)->name;
						$fullPrice += $product->price;
					}
        }

	      $view->products = $products; //add products to the view so we can later display them
				$view->fullPrice = $fullPrice;
			}
			$view->display();
  }

  public function addToCart() {
      if (isset($_GET['id'])) {
        $productId = $_GET['id'];
      } else {
        throw new Exception("No id", 1);
        //TODO
      }
      echo $productId;
      if (empty($_SESSION['cart']['products'])) {
        $_SESSION['cart']['products'] = array();
      }

      if (!empty($_SESSION['username'])) {
        $_SESSION['cart']['user'] = $_SESSION['username'];
      }

      //TODO: only add if id doesn't already exist
      $_SESSION['cart']['products'][count($_SESSION['cart']['products'])] = $productId;
  }

  public function removeFromCart() {
      if (isset($_GET['id'])) {
        $productId = $_GET['id'];
      } else {
        throw new Exception("No id", 1);
        //TODO
      }

      //can only remove the first instance
      $key = array_search($productId, $_SESSION['cart']['products']);

      //removes the element from the array and keeps indexes correct
      array_splice($_SESSION['cart']['products'], $key, 1);
    }

	public function save() {
		if (!empty($_SESSION['cart']['products']) && isset($_SESSION['username'])) {
			//just to be save
			$userRepository = new UserRepository();
			$cartRepository = new CartRepository();

			$cart = $_SESSION['cart'];
			$productIds = $cart['products'];
			$username = $_SESSION['username'];
			$uid = $userRepository->getId($username)->id;

			foreach ($productIds as $pid) {
				$cartRepository->create($uid, $pid);
			}
		}
	}
}
