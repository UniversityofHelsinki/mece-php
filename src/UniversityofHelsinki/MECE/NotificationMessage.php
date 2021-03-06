<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

namespace UniversityofHelsinki\MECE;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use LogicException;

/**
 * Class NotificationMessage
 * @package UniversityofHelsinki\MECE
 *
 * Extends basic message class an represents an notification message.
 *
 * @author Mikael Kundert <mikael.kundert@wunderkraut.com>
 */
class NotificationMessage extends Message {

  /**
   * @var DateTime
   */
  protected $deadline;

  /**
   * @var DateTime
   */
  protected $expiration;

  /**
   * @var DateTime
   */
  protected $submitted;

  /**
   * @var MultilingualStringValue
   */
  protected $heading;

  /**
   * @var MultilingualStringValue
   */
  protected $message;

  /**
   * @var MultilingualStringValue
   */
  protected $linkText;

  /**
   * @var MultilingualStringValue
   */
  protected $linkUrl;

  /**
   * @var string
   */
  protected $avatarImageUrl = '';

  /*
   * Following list of properties are for this class implementation.
   */

  /**
   * @var DateTimeZone
   */
  private $requiredTimeZone;

  public function __construct(array $recipients, $source, array $options = []) {
    parent::__construct($recipients, $source, $options);

    // Construct date values with required timezone
    $this->requiredTimeZone = new DateTimeZone('Etc/Zulu');
    $this->deadline = new DateTime('now', $this->requiredTimeZone);
    $this->expiration = new DateTime('now', $this->requiredTimeZone);
    $this->submitted = new DateTime('now', $this->requiredTimeZone);
  }

  /**
   * @param DateTime $deadline
   * @return void
   */
  public function setDeadline(DateTime $deadline) {

    // Deadline can't be after expiration
    if ($deadline > $this->getExpiration()) {
      throw new LogicException('Deadline can not be after expiration.');
    }

    $this->setDateProperty($deadline, 'deadline');
  }

  /**
   * @return DateTime
   */
  public function getDeadline() {
    return $this->deadline;
  }

  /**
   * @param DateTime $expiration
   * @return void
   */
  public function setExpiration(DateTime $expiration) {

    // Expiration can't be before submitted or deadline
    if ($expiration < $this->getSubmitted()) {
      throw new LogicException('Expiration can not be before submitted.');
    }
    if ($expiration < $this->getDeadline()) {
      throw new LogicException('Expiration can not be before deadline.');
    }

    $this->setDateProperty($expiration, 'expiration');
  }

  /**
   * @return DateTime
   */
  public function getExpiration() {
    return $this->expiration;
  }

  /**
   * @param DateTime $submitted
   * @return void
   */
  public function setSubmitted(DateTime $submitted) {

    // Submitted can't be after expiration
    if ($submitted > $this->getExpiration()) {
      throw new LogicException('Submitted can not be after expiration.');
    }

    $this->setDateProperty($submitted, 'submitted');
  }

  /**
   * @return mixed
   */
  public function getSubmitted() {
    return $this->submitted;
  }

  /**
   * @param MultilingualStringValue $heading
   * @return void
   */
  public function setHeading(MultilingualStringValue $heading) {
    $this->heading = $heading;
  }

  /**
   * @return MultilingualStringValue
   */
  public function getHeading() {
    return $this->heading;
  }

  /**
   * @param MultilingualStringValue $message
   * @return void
   */
  public function setMessage(MultilingualStringValue $message) {
    $this->message = $message;
  }

  /**
   * @return MultilingualStringValue
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param MultilingualStringValue $linkText
   * @return void
   */
  public function setLinkText(MultilingualStringValue $linkText) {
    $this->linkText = $linkText;
  }

  /**
   * @return MultilingualStringValue
   */
  public function getLinkText() {
    return $this->linkText;
  }

  /**
   * @param MultilingualStringValue $link
   * @return void
   */
  public function setLinkUrl(MultilingualStringValue $link) {
    $this->linkUrl = $link;
  }

  /**
   * @return MultilingualStringValue
   */
  public function getLinkUrl() {
    return $this->linkUrl;
  }

  /**
   * @param MultilingualStringValue $link
   * @return void
   * @deprecated Use NotificationMessage::setLinkUrl() instead.
   */
  public function setLink(MultilingualStringValue $link) {
    $this->setLinkUrl($link);
  }

  /**
   * @return MultilingualStringValue
   * @deprecated Use NotificationMessage::getLinkUrl() instead.
   */
  public function getLink() {
    return $this->getLinkUrl();
  }

  /**
   * @param string $avatarImageUrl
   * @return void
   */
  public function setAvatarImageUrl($avatarImageUrl) {
    $this->setStringProperty($avatarImageUrl, 'avatarImageUrl');
  }

  /**
   * @return string
   */
  public function getAvatarImageUrl() {
    return $this->avatarImageUrl;
  }

  /**
   * Exports the message object as JSON string.
   * @return string
   */
  public function export() {
    $properties = [
      'recipients',
      'priority',
      'deadline',
      'expiration',
      'submitted',
      'source',
      'sourceId',
      'heading',
      'message',
      'linkText',
      'linkUrl',
      'avatarImageUrl',
    ];
    $export = new \stdClass();
    foreach ($properties as $property) {

      // Define getter method for property and ensure it exists
      $getterMethod = 'get' . ucfirst($property);
      if (!method_exists($this, $getterMethod)) {
        throw new LogicException('Getter method "' . $getterMethod . '" was not found.');
      }

      // Call the getter method and set the value to $export in certain way that
      // depends what type it is, but only if it's not empty.
      $value = $this->$getterMethod();
      if (!empty($value)) {
        if (is_string($value) || is_array($value)) {
          $export->{$property} = $value;
        }
        elseif ($value instanceof DateTime) {
          $export->{$property} = $value->format('Y-m-d\TH:i:s\Z');
        }
        elseif ($value instanceof MultilingualStringValue) {

          // Loop each supported language and set it as multilingual value. Same
          // time try to specify the language neutral value that will be set after
          // the loop.
          $languageNeutralValue = '';
          foreach ($this->supportedLanguages as $language) {

            // Set multilingual value
            $multilingualValue = $value->getValue($language);
            $multilingualProperty = $property . strtoupper($language);
            $export->{$multilingualProperty} = $multilingualValue;

            // This should get the first non-empty value.
            if (empty($languageNeutralValue)) {
              $languageNeutralValue = $multilingualValue;
            }
          }

          // Set the language neutral value too
          $export->{$property} = $languageNeutralValue;

        }
      }
    }
    return json_encode($export);
  }

  /**
   * An internal private method for setting date time value that validates the
   * value against required timezone.
   *
   * @param DateTime $value
   * @param $property
   */
  private function setDateProperty(DateTime $value, $property) {

    // Check that given $property is string
    if (!is_string($property)) {
      throw new InvalidArgumentException("Property should be type of string.");
    }

    // Check that property is found and its type of string
    if (!isset($this->{$property}) || (!isset($this->$property) && get_class($this->{$property}) == 'DateTime')) {
      throw new LogicException("There is no such DateTime property as '$property'");
    }

    // Check that value matches with required timezone
    if ($value->getTimezone()->getName() !== $this->requiredTimeZone->getName()) {
      throw new LogicException($property . ' DateTime value must be in timezone "' . $this->requiredTimeZone->getName() . '"');
    }

    $this->{$property} = $value;
  }
}
