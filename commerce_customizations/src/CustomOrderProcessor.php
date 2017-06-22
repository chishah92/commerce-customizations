<?php

namespace Drupal\commerce_customizations;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Adjustment;

/**
 * Provides an order processor that modifies the cart according to the business logic.
 */
class CustomOrderProcessor implements OrderProcessorInterface
{
  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order)
  {
    foreach ($order->getItems() as $order_item) {
      // SetAdjustment to empty initially.
      $order_item->setAdjustments([]);
      $product_variation = $order_item->getPurchasedEntity();
      $default_product_variation_id = $order_item->getPurchasedEntityId();
      if (!empty($product_variation)) {
        $product_id = $product_variation->get('product_id')
          ->getValue()[0]['target_id'];
        $product = Product::load($product_id);
        $product_type = $product->get('type')->getValue()[0]['target_id'];
        $quantity = $order_item->getQuantity();
        $product_title = $product->getTitle();
        if ($product_type == 'default') {
          $product_price = $order_item->getUnitPrice();
          $product_unit_price = $product_price->getNumber();
          if ($product_unit_price < 20 && $quantity <= 10) {
            $new_adjustment = $product_unit_price;
          }
          elseif ($product_unit_price < 20 && $quantity > 10) {
            $new_adjustment = ($product_unit_price * 10) / $quantity;
          }
          else {
            continue;
          }
          $adjustments = $order_item->getAdjustments();
          // Apply custom adjustment.
          $adjustments[] = new Adjustment([
            'type' => 'custom_adjustment',
            'label' => 'Discounted Price - ' . $product_title,
            'amount' => new Price('-' . $new_adjustment, 'USD'),
          ]);
          $order_item->setAdjustments($adjustments);
        //  $order_item->save();
          $adjustments;
        }
      }
    }
  }
}


