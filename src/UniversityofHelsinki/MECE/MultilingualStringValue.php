<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

namespace UniversityofHelsinki\MECE;

use InvalidArgumentException;

/**
 * Class MultilingualStringValue
 * @package UniversityofHelsinki\MECE
 *
 * Provides an class for containing multilingual strings.
 *
 * @author Mikael Kundert <mikael.kundert@wunderkraut.com>
 */
class MultilingualStringValue {
  use VersionTrait;

  /**
   * @var array
   */
  private $supportedLanguages = [];

  /**
   * @var array
   */
  private $values = [];

  /**
   * Constructor of MultilingualStringValue class. You may optionally pass
   * options for the class.
   *
   * @param array $options
   *   'supportedLanguages': List of supported languages. If not given, then
   *                         constructor will set default values for you.
   */
  public function __construct(array $options = []) {

    // Set supported languages
    if (!isset($options['supportedLanguages'])) {
      $options['supportedLanguages'] = ['fi', 'en', 'sv'];
    }
    $this->setSupportedLanguages($options['supportedLanguages']);
  }

  /**
   * Setter callback for setting a value for specific language.
   * @param string $value
   * @param string $language
   * @return void
   */
  public function setValue($value, $language) {

    // Ensure that language is a string and that's supported language
    if (!is_string($language)) {
      throw new InvalidArgumentException('Language must be an string type.');
    }
    if (!in_array($language, $this->getSupportedLanguages())) {
      throw new InvalidArgumentException('Language "' . $language . '" is not supported.');
    }

    // Ensure that value is a string
    if (!is_string($value)) {
      throw new InvalidArgumentException('Value must be an string type.');
    }

    // Set the value to given language
    $values = $this->getValues();
    $values[$language] = $value;
    $this->setValues($values);
  }

  /**
   * @param string $language
   * @return string|null
   *   Returns an string of given language when available. Returns NULL if value
   *   is not set for given language.
   */
  public function getValue($language) {
    // Ensure that language is a string and that's supported language
    if (!is_string($language)) {
      throw new InvalidArgumentException('Language must be an string type.');
    }
    if (!in_array($language, $this->getSupportedLanguages())) {
      throw new InvalidArgumentException('Language "' . $language . '" is not supported.');
    }

    $values = $this->getValues();
    return isset($values[$language]) ? $values[$language] : NULL;
  }

  /**
   * @param array $supportedLanguages
   */
  public function setSupportedLanguages(array $supportedLanguages) {
    $this->supportedLanguages = $supportedLanguages;
  }

  /**
   * @return array
   */
  public function getSupportedLanguages() {
    return $this->supportedLanguages;
  }

  /**
   * @param array $values
   */
  public function setValues(array $values) {
    $this->values = $values;
  }

  /**
   * @return array
   */
  public function getValues() {
    return $this->values;
  }
}
