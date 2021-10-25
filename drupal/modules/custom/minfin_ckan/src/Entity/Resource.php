<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Commenting.FunctionComment.MissingParamComment
// phpcs:disable Drupal.Commenting.FunctionComment.MissingReturnComment
namespace Drupal\minfin_ckan\Entity;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * A CKAN Resource entity.
 */
class Resource {

  /**
   * The internal unique CKAN id.
   *
   * @var string
   */
  public $id;

  /**
   * The internal CKAN packageId.
   *
   * @var string
   */
  public $packageId;

  /**
   * @var string
   */
  public $url;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $description;

  /**
   * @var string
   */
  public $metadataLanguage;

  /**
   * @var string[]
   */
  public $language;

  /**
   * @var string
   */
  public $licenseId;

  /**
   * @var string
   */
  public $format;

  /**
   * @var int|null
   */
  public $size;

  /**
   * @var string[]|null
   */
  public $downloadUrl;

  /**
   * @var string|null
   */
  public $mimetype;

  /**
   * @var string|null
   */
  public $releaseDate;

  /**
   * @var string|null
   */
  public $rights;

  /**
   * @var string|null
   */
  public $status;

  /**
   * @var bool
   */
  public $linkStatus;

  /**
   * @var string|null
   */
  public $linkStatusLastChecked;

  /**
   * @var string|null
   */
  public $modificationDate;

  /**
   * @var string[]|null
   */
  public $linkedSchemas;

  /**
   * @var string|null
   */
  public $hash;

  /**
   * @var string|null
   */
  public $hashAlgorithm;

  /**
   * @var string[]|null
   */
  public $documentation;

  /**
   * @var string|null
   */
  public $resourceType;

  /**
   * @var \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public $created;

  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId($id): void {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getPackageId(): string {
    return $this->packageId;
  }

  /**
   * @param string $packageId
   */
  public function setPackageId($packageId): void {
    $this->packageId = $packageId;
  }

  /**
   * @return string
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * @param string $url
   */
  public function setUrl($url): void {
    $this->url = $url;
  }

  /**
   * @return string
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * @param string $description
   */
  public function setDescription($description): void {
    $this->description = $description;
  }

  /**
   * @return string
   */
  public function getFormat(): string {
    return $this->format;
  }

  /**
   * @param string $format
   */
  public function setFormat($format): void {
    $this->format = $format;
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name): void {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getMetadataLanguage(): string {
    return $this->metadataLanguage;
  }

  /**
   * @param string $metadataLanguage
   */
  public function setMetadataLanguage($metadataLanguage): void {
    $this->metadataLanguage = $metadataLanguage;
  }

  /**
   * @return string[]
   */
  public function getLanguage(): array {
    return $this->language;
  }

  /**
   * @param string[] $language
   */
  public function setLanguage(array $language): void {
    $this->language = $language;
  }

  /**
   * @return string
   */
  public function getLicenseId(): string {
    return $this->licenseId;
  }

  /**
   * @param string $licenseId
   */
  public function setLicenseId($licenseId): void {
    $this->licenseId = $licenseId;
  }

  /**
   * @return int|null
   */
  public function getSize(): ?int {
    return $this->size;
  }

  /**
   * @param int|null $size
   */
  public function setSize($size): void {
    $this->size = $size;
  }

  /**
   * @return null|string[]
   */
  public function getDownloadUrl(): ?array {
    return $this->downloadUrl;
  }

  /**
   * @param null|string[] $downloadUrl
   */
  public function setDownloadUrl($downloadUrl): void {
    $this->downloadUrl = $downloadUrl;
  }

  /**
   * @return null|string
   */
  public function getMimetype(): ?string {
    return $this->mimetype;
  }

  /**
   * @param null|string $mimetype
   */
  public function setMimetype($mimetype): void {
    $this->mimetype = $mimetype;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getReleaseDate(): ?DrupalDateTime {
    return $this->formatDate($this->releaseDate);
  }

  /**
   * @param null|string $releaseDate
   */
  public function setReleaseDate($releaseDate): void {
    $this->releaseDate = $releaseDate;
  }

  /**
   * @return null|string
   */
  public function getRights(): ?string {
    return $this->rights;
  }

  /**
   * @param null|string $rights
   */
  public function setRights($rights): void {
    $this->rights = $rights;
  }

  /**
   * @return null|string
   */
  public function getStatus(): ?string {
    return $this->status;
  }

  /**
   * @param null|string $status
   */
  public function setStatus($status): void {
    $this->status = $status;
  }

  /**
   * @return bool
   */
  public function getLinkStatus(): bool {
    return $this->linkStatus;
  }

  /**
   * @param bool $linkStatus
   */
  public function setLinkStatus($linkStatus): void {
    $this->linkStatus = $linkStatus;
  }

  /**
   * @return null|string
   */
  public function getLinkStatusLastChecked(): ?string {
    return $this->linkStatusLastChecked;
  }

  /**
   * @param null|string $linkStatusLastChecked
   */
  public function setLinkStatusLastChecked($linkStatusLastChecked): void {
    $this->linkStatusLastChecked = $linkStatusLastChecked;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getModificationDate(): ?DrupalDateTime {
    return $this->formatDate($this->modificationDate);
  }

  /**
   * @param null|string $modificationDate
   */
  public function setModificationDate($modificationDate): void {
    $this->modificationDate = $modificationDate;
  }

  /**
   * @return null|string[]
   */
  public function getLinkedSchemas(): ?array {
    return $this->linkedSchemas;
  }

  /**
   * @param null|string[] $linkedSchemas
   */
  public function setLinkedSchemas($linkedSchemas): void {
    $this->linkedSchemas = $linkedSchemas;
  }

  /**
   * @return null|string
   */
  public function getHash(): ?string {
    return $this->hash;
  }

  /**
   * @param null|string $hash
   */
  public function setHash($hash): void {
    $this->hash = $hash;
  }

  /**
   * @return null|string
   */
  public function getHashAlgorithm(): ?string {
    return $this->hashAlgorithm;
  }

  /**
   * @param null|string $hashAlgorithm
   */
  public function setHashAlgorithm($hashAlgorithm): void {
    $this->hashAlgorithm = $hashAlgorithm;
  }

  /**
   * @return null|string[]
   */
  public function getDocumentation(): ?array {
    return $this->documentation;
  }

  /**
   * @param null|string[] $documentation
   */
  public function setDocumentation($documentation): void {
    $this->documentation = $documentation;
  }

  /**
   * Helper function to turn the ISO date string into an DrupalDateTime object.
   *
   * @param string $string
   *   The date as an ISO date string.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  private function formatDate($string): ?DrupalDateTime {
    if (!empty($string)) {
      return new DrupalDateTime($string);
    }

    return NULL;
  }

  /**
   * Return the config type for this resource.
   *
   * @return string
   */
  public function getResourceType(): string {
    if (!empty($this->resourceType)) {
      return $this->resourceType;
    }

    $config = \Drupal::configFactory()->get('ckan.resourcetype.settings');
    if (in_array($this->getFormat(), $config->get('webservice') ?? [], TRUE)) {
      return $this->resourceType = 'webservices';
    }

    if (in_array($this->getFormat(), $config->get('documentation') ?? [], TRUE)) {
      return $this->resourceType = 'documentation';
    }

    return $this->resourceType = 'downloadable-files';
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getCreated(): ?DrupalDateTime {
    return $this->created;
  }

  /**
   * @param string $created
   */
  public function setCreated(string $created) {
    $this->created = $created;
  }

}
