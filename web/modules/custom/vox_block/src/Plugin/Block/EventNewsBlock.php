<?php

namespace Drupal\vox_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "vox_block_event",
 *   admin_label = @Translation("Event News"),
 *   category = @Translation("Vox Block")
 * )
 */
class EventNewsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $date_now = date('Y-m-d');
    $large_style = \Drupal::entityTypeManager()->getStorage('image_style')->load('large');
    // Get 2 on going Event contents
    $event_query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $orGroup = $event_query->orConditionGroup()
      ->condition('field_event_date.end_value', $date_now, '>=')
      ->condition('field_event_date.value', $date_now, '>=');
    $event_query->condition('type', 'event')
      ->condition($orGroup)
      ->condition('status', 1);
    $event_query->sort('field_event_date.value', 'ASC')
      ->range(0, 2);
    $event_nids = $event_query->execute();

    $event_nodes = array();
    foreach ($event_nids as $enid) {
      $event_node = \Drupal::entityTypeManager()->getStorage('node')->load($enid);
      $image_uri = $event_node->field_vox_image->entity->getFileUri();
      $date_start_stamp = date_create($event_node->field_event_date->value);
      $date_start = date_format($date_start_stamp, 'j F Y');
      $date_end = '';
      if ($event_node->field_event_date->end_value) {
        $date_end_stamp = date_create($event_node->field_event_date->end_value);
        $date_end = ' - ' . date_format($date_end_stamp, 'j F Y');
      }
      $description = substr($event_node->body->value, 0, 130);
      $description = substr($description, 0, strrpos($description, " ")) . "...</p>";
      $event_nodes[] = [
        'title' => $event_node->label(),
        'image_full' => file_create_url($image_uri),
        'image_large' => $large_style->buildUrl($image_uri),
        'date' => $date_start . $date_end,
        'body' => $description,
        'path' => $event_node->toUrl()->toString(),
      ];
    }

    // Get latest news contents
    $news_query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $news_query->condition('type', 'news')
      ->condition('status', 1);
    $news_query->sort('created', 'DESC')
      ->range(0, 1);
    $news_nids = $news_query->execute();

    $news_nodes = array();
    foreach ($news_nids as $nnid) {
      $news_node = \Drupal::entityTypeManager()->getStorage('node')->load($nnid);
      $image_uri = $news_node->field_vox_image->entity->getFileUri();
      $date = date_create($news_node->field_publish_date->value);
      $description = substr($news_node->body->value, 0, 130);
      $description = substr($description, 0, strrpos($description, " ")) . "...</p>";
      $news_nodes[] = [
        'title' => $news_node->label(),
        'image_full' => file_create_url($image_uri),
        'image_large' => $large_style->buildUrl($image_uri),
        'date' => date_format($date, 'j F Y'),
        'body' => $description,
        'path' => $news_node->toUrl()->toString(),
      ];
    }

    //dump($news_nodes);
    $build = [
      '#theme' => 'vox_event_news_block',
      '#event_nodes' => $event_nodes,
      '#news_nodes' => $news_nodes,
    ];

    // $build['content'] = [
    //   '#markup' => $this->t('It works!'),
    // ];
    return $build;
    
  }

}
