<?php

namespace Drupal\kelpgate\EventSubscriber;

use Drupal\file\Validation\FileValidationEvent;
use Drupal\kelpgate\Scanner\KelpgateScanner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Scans uploads during file validation on Drupal 10.2 and later.
 *
 * Drupal 10.2 replaced hook_file_validate() with FileValidationEvent, and Drupal 11 no longer
 * invokes the hook. On earlier versions the event is never dispatched and
 * kelpgate_file_validate() scans instead.
 */
class FileValidationSubscriber implements EventSubscriberInterface {

  /**
   * The Kelpgate scanner.
   *
   * @var \Drupal\kelpgate\Scanner\KelpgateScanner
   */
  protected $scanner;

  /**
   * Constructs a FileValidationSubscriber.
   */
  public function __construct(KelpgateScanner $scanner) {
    $this->scanner = $scanner;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // FileValidationEvent::class is resolved at compile time, so this is safe on versions where
    // the class does not exist; the event is then simply never dispatched.
    return [
      FileValidationEvent::class => 'onFileValidation',
    ];
  }

  /**
   * Rejects the file when the scanner does not accept it.
   */
  public function onFileValidation(FileValidationEvent $event): void {
    $error = $this->scanner->validationError($event->file);
    if ($error === NULL) {
      return;
    }
    $event->violations->add(new ConstraintViolation($error, (string) $error, [], $event->file, '', NULL));
  }

}
