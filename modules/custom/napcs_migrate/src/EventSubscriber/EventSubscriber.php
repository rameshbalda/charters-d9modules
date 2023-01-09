<?php

namespace Drupal\napcs_migrate\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\redirect\Entity\Redirect;
use Drupal\redirect\RedirectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for NAPCS migrate module.
 */
class EventSubscriber implements EventSubscriberInterface {

  /**
   * The redirect repository.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $redirectRepo;

  /**
   * Class constructor.
   */
  public function __construct(RedirectRepository $redirect_repo) {
    $this->redirectRepo = $redirect_repo;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE][] = ['blogRedirect'];
    return $events;
  }

  /**
   * Create redirects for migrated blog posts.
   */
  public function blogRedirect(MigratePostRowSaveEvent $event) {
    if ($event->getMigration()->id() == 'napcs_blog') {
      $url = $event->getRow()->getSourceProperty('url');
      $redirect_source = ltrim(parse_url($url, PHP_URL_PATH), '/');
      $nid = reset($event->getDestinationIdValues());
      $redirect_redirect = "node/$nid";
      // Check if redirect from this path already exists. This might happen if
      // you are testing this migration ad nauseum.
      if ($redirect = reset($this->redirectRepo->findBySourcePath($redirect_source))) {
        // If it's not already directing to this node, do nothing and post a
        // warning message.
        if (strpos($redirect->getRedirect()['uri'], $redirect_redirect) === FALSE) {
          \Drupal::messenger()->addWarning(t('Source path %redirect_source is already used in a redirect.', ['%redirect_source' => $redirect_source]));
          return;
        }
      }
      else {
        // If no redirect exists, create a fresh one.
        $redirect = Redirect::create();
      }
      $redirect->setSource($redirect_source);
      $redirect->setRedirect($redirect_redirect);
      $redirect->setStatusCode(301);
      $redirect->save();
    }
  }

}
