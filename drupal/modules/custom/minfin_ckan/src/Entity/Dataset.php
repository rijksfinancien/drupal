<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Commenting.FunctionComment.MissingParamComment
// phpcs:disable Drupal.Commenting.FunctionComment.MissingReturnComment
namespace Drupal\minfin_ckan\Entity;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * A CKAN Dataset entity.
 */
class Dataset {

  /**
   * The internal unique CKAN id.
   *
   * @var string|null
   */
  public $id;

  /**
   * @var string
   */
  public $ownerOrg;

  /**
   * @var string
   */
  public $identifier;

  /**
   * @var string[]
   */
  public $alternateIdentifier = [];

  /**
   * @var string[]
   */
  public $language = [];

  /**
   * @var string|null
   */
  public $sourceCatalog;

  /**
   * @var string
   */
  public $authority;

  /**
   * @var string
   */
  public $publisher;

  /**
   * @var string|null
   */
  public $contactPointEmail;

  /**
   * @var string|null
   */
  public $contactPointAddress;

  /**
   * @var string
   */
  public $contactPointName;

  /**
   * @var string|null
   */
  public $contactPointPhone;

  /**
   * @var string|null
   */
  public $contactPointWebsite;

  /**
   * @var string|null
   */
  public $contactPointTitle;

  /**
   * @var string|null
   */
  public $accessRights;

  /**
   * @var string|null
   */
  public $url;

  /**
   * @var string[]
   */
  public $conformsTo = [];

  /**
   * @var string[]
   */
  public $relatedResource = [];

  /**
   * @var string[]
   */
  public $source = [];

  /**
   * @var string|null
   */
  public $version;

  /**
   * @var string[]
   */
  public $versionNotes = [];

  /**
   * @var string[]
   */
  public $hasVersion = [];

  /**
   * @var string[]
   */
  public $isVersionOf = [];

  /**
   * @var string|null
   */
  public $legalFoundationRef;

  /**
   * @var string|null
   */
  public $legalFoundationUri;

  /**
   * @var string|null
   */
  public $legalFoundationLabel;

  /**
   * @var string|null
   */
  public $frequency;

  /**
   * @var string[]
   */
  public $provenance = [];

  /**
   * @var string[]
   */
  public $documentation = [];

  /**
   * @var string[]
   */
  public $sample = [];

  /**
   * @var string
   */
  public $licenseId;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $title;

  /**
   * @var string
   */
  public $notes;

  /**
   * @var Tag[]
   */
  public $tags = [];

  /**
   * @var string
   */
  public $metadataLanguage;

  /**
   * @var string|null
   */
  public $metadataModified;

  /**
   * @var string[]
   */
  public $theme = [];

  /**
   * @var string
   */
  public $modified;

  /**
   * @var string|null
   */
  public $issued;

  /**
   * @var string[]
   */
  public $spatialScheme = [];

  /**
   * @var string[]
   */
  public $spatialValue = [];

  /**
   * @var string|null
   */
  public $temporalLabel;

  /**
   * @var string|null
   */
  public $temporalStart;

  /**
   * @var string|null
   */
  public $temporalEnd;

  /**
   * @var string|null
   */
  public $datasetStatus;

  /**
   * The status of the resource links.
   *
   * @var int
   */
  public $datasetLinkStatus;

  /**
   * @var string|null
   */
  public $datePlanned;

  /**
   * @var Resource[]
   */
  public $resources;

  /**
   * @var bool
   */
  public $private;

  /**
   * @var bool
   */
  public $highValue;

  /**
   * @var bool
   */
  public $baseRegister;

  /**
   * @var bool
   */
  public $referenceData;

  /**
   * @var bool
   */
  public $nationalCoverage;

  /**
   * @var string[]
   */
  public $communities = [];

  /**
   * @var string|null
   */
  public $datasetQuality;

  /**
   * @var string|null
   */
  public $creatorUserId;

  /**
   * @var array
   */
  public $sortedResources;

  /**
   * @return string|null
   */
  public function getId(): ?string {
    return $this->id;
  }

  /**
   * @param string|null $id
   */
  public function setId(string $id): void {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getOwnerOrg(): string {
    return $this->ownerOrg;
  }

  /**
   * @param string $ownerOrg
   */
  public function setOwnerOrg(string $ownerOrg): void {
    $this->ownerOrg = $ownerOrg;
  }

  /**
   * @return string
   */
  public function getIdentifier(): string {
    return $this->identifier;
  }

  /**
   * @param string $identifier
   */
  public function setIdentifier(string $identifier): void {
    $this->identifier = $identifier;
  }

  /**
   * @return string[]
   */
  public function getAlternateIdentifier(): array {
    return $this->alternateIdentifier;
  }

  /**
   * @param string[] $alternateIdentifier
   */
  public function setAlternateIdentifier(array $alternateIdentifier): void {
    $this->alternateIdentifier = $alternateIdentifier;
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
   * @return string|null
   */
  public function getSourceCatalog(): ?string {
    return $this->sourceCatalog;
  }

  /**
   * @param string|null $sourceCatalog
   */
  public function setSourceCatalog(string $sourceCatalog = NULL): void {
    $this->sourceCatalog = $sourceCatalog;
  }

  /**
   * @return string
   */
  public function getAuthority(): string {
    return $this->authority;
  }

  /**
   * @param string $authority
   */
  public function setAuthority(string $authority): void {
    $this->authority = $authority;
  }

  /**
   * @return string
   */
  public function getPublisher(): string {
    return $this->publisher;
  }

  /**
   * @param string $publisher
   */
  public function setPublisher(string $publisher): void {
    $this->publisher = $publisher;
  }

  /**
   * @return string|null
   */
  public function getContactPointEmail(): ?string {
    return $this->contactPointEmail;
  }

  /**
   * @param string|null $contactPointEmail
   */
  public function setContactPointEmail(string $contactPointEmail = NULL): void {
    $this->contactPointEmail = $contactPointEmail;
  }

  /**
   * @return string|null
   */
  public function getContactPointAddress(): ?string {
    return $this->contactPointAddress;
  }

  /**
   * @param string|null $contactPointAddress
   */
  public function setContactPointAddress(string $contactPointAddress = NULL): void {
    $this->contactPointAddress = $contactPointAddress;
  }

  /**
   * @return string
   */
  public function getContactPointName(): string {
    return $this->contactPointName;
  }

  /**
   * @param string $contactPointName
   */
  public function setContactPointName(string $contactPointName): void {
    $this->contactPointName = $contactPointName;
  }

  /**
   * @return string|null
   */
  public function getContactPointPhone(): ?string {
    return $this->contactPointPhone;
  }

  /**
   * @param string|null $contactPointPhone
   */
  public function setContactPointPhone(string $contactPointPhone = NULL): void {
    $this->contactPointPhone = $contactPointPhone;
  }

  /**
   * @return string|null
   */
  public function getContactPointWebsite(): ?string {
    return $this->contactPointWebsite;
  }

  /**
   * @param string|null $contactPointWebsite
   */
  public function setContactPointWebsite(string $contactPointWebsite = NULL): void {
    $this->contactPointWebsite = $contactPointWebsite;
  }

  /**
   * @return string|null
   */
  public function getContactPointTitle(): ?string {
    return $this->contactPointTitle;
  }

  /**
   * @param string|null $contactPointTitle
   */
  public function setContactPointTitle(string $contactPointTitle = NULL): void {
    $this->contactPointTitle = $contactPointTitle;
  }

  /**
   * @return string|null
   */
  public function getAccessRights(): ?string {
    return $this->accessRights;
  }

  /**
   * @param string|null $accessRights
   */
  public function setAccessRights(string $accessRights = NULL): void {
    $this->accessRights = $accessRights;
  }

  /**
   * @return string|null
   */
  public function getUrl(): ?string {
    return $this->url;
  }

  /**
   * @param string|null $url
   */
  public function setUrl(string $url = NULL): void {
    $this->url = $url;
  }

  /**
   * @return string[]
   */
  public function getConformsTo(): array {
    return $this->conformsTo;
  }

  /**
   * @param string[] $conformsTo
   */
  public function setConformsTo(array $conformsTo): void {
    $this->conformsTo = $conformsTo;
  }

  /**
   * @return string[]
   */
  public function getRelatedResource(): array {
    return $this->relatedResource;
  }

  /**
   * @param string[] $relatedResource
   */
  public function setRelatedResource(array $relatedResource): void {
    $this->relatedResource = $relatedResource;
  }

  /**
   * @return string[]
   */
  public function getSource(): array {
    return $this->source;
  }

  /**
   * @param string[] $source
   */
  public function setSource(array $source): void {
    $this->source = $source;
  }

  /**
   * @return string|null
   */
  public function getVersion(): ?string {
    return $this->version;
  }

  /**
   * @param string|null $version
   */
  public function setVersion(string $version = NULL): void {
    $this->version = $version;
  }

  /**
   * @return string[]
   */
  public function getVersionNotes(): array {
    return $this->versionNotes;
  }

  /**
   * @param string[] $versionNotes
   */
  public function setVersionNotes(array $versionNotes): void {
    $this->versionNotes = $versionNotes;
  }

  /**
   * @return string[]
   */
  public function getHasVersion(): array {
    return $this->hasVersion;
  }

  /**
   * @param string[] $hasVersion
   */
  public function setHasVersion(array $hasVersion): void {
    $this->hasVersion = $hasVersion;
  }

  /**
   * @return string[]
   */
  public function getIsVersionOf(): array {
    return $this->isVersionOf;
  }

  /**
   * @param string[] $isVersionOf
   */
  public function setIsVersionOf(array $isVersionOf): void {
    $this->isVersionOf = $isVersionOf;
  }

  /**
   * @return string|null
   */
  public function getLegalFoundationRef(): ?string {
    return $this->legalFoundationRef;
  }

  /**
   * @param string|null $legalFoundationRef
   */
  public function setLegalFoundationRef(string $legalFoundationRef = NULL): void {
    $this->legalFoundationRef = $legalFoundationRef;
  }

  /**
   * @return string|null
   */
  public function getLegalFoundationUri(): ?string {
    return $this->legalFoundationUri;
  }

  /**
   * @param string|null $legalFoundationUri
   */
  public function setLegalFoundationUri(string $legalFoundationUri = NULL): void {
    $this->legalFoundationUri = $legalFoundationUri;
  }

  /**
   * @return string|null
   */
  public function getLegalFoundationLabel(): ?string {
    return $this->legalFoundationLabel;
  }

  /**
   * @param string|null $legalFoundationLabel
   */
  public function setLegalFoundationLabel(string $legalFoundationLabel = NULL): void {
    $this->legalFoundationLabel = $legalFoundationLabel;
  }

  /**
   * @return string|null
   */
  public function getFrequency(): ?string {
    return $this->frequency;
  }

  /**
   * @param string|null $frequency
   */
  public function setFrequency(string $frequency = NULL): void {
    $this->frequency = $frequency;
  }

  /**
   * @return string[]
   */
  public function getProvenance(): array {
    return $this->provenance;
  }

  /**
   * @param string[] $provenance
   */
  public function setProvenance(array $provenance): void {
    $this->provenance = $provenance;
  }

  /**
   * @return string[]
   */
  public function getSample(): array {
    return $this->sample;
  }

  /**
   * @param string[] $sample
   */
  public function setSample(array $sample): void {
    $this->sample = $sample;
  }

  /**
   * @return string[]
   */
  public function getDocumentation(): array {
    return $this->documentation;
  }

  /**
   * @param string[] $documentation
   */
  public function setDocumentation(array $documentation): void {
    $this->documentation = $documentation;
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
  public function setLicenseId(string $licenseId): void {
    $this->licenseId = $licenseId;
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
  public function setName(string $name): void {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * @param string $title
   */
  public function setTitle(string $title): void {
    $this->title = $title;
  }

  /**
   * @return string
   */
  public function getNotes(): string {
    return $this->notes;
  }

  /**
   * @param string $notes
   */
  public function setNotes(string $notes): void {
    $this->notes = $notes;
  }

  /**
   * @return Tag[]
   */
  public function getTags(): ?array {
    return $this->tags;
  }

  /**
   * @param Tag[] $tags
   */
  public function setTags(array $tags): void {
    $this->tags = $tags;
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
  public function setMetadataLanguage(string $metadataLanguage): void {
    $this->metadataLanguage = $metadataLanguage;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getMetadataModified(): ?DrupalDateTime {
    return $this->formatDate($this->metadataModified);
  }

  /**
   * @param string|null $metadataModified
   */
  public function setMetadataModified(string $metadataModified = NULL): void {
    $this->metadataModified = $metadataModified;
  }

  /**
   * @return string[]
   */
  public function getTheme(): array {
    return $this->theme;
  }

  /**
   * @param string[] $theme
   */
  public function setTheme(array $theme): void {
    $this->theme = $theme;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getModified(): ?DrupalDateTime {
    return $this->formatDate($this->modified);
  }

  /**
   * @param string $modified
   */
  public function setModified(string $modified): void {
    $this->modified = $modified;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getIssued(): ?DrupalDateTime {
    return $this->formatDate($this->issued);
  }

  /**
   * @param string|null $issued
   */
  public function setIssued(string $issued = NULL): void {
    $this->issued = $issued;
  }

  /**
   * @return string[]
   */
  public function getSpatialScheme(): array {
    return $this->spatialScheme;
  }

  /**
   * @param string[] $spatialScheme
   */
  public function setSpatialScheme(array $spatialScheme): void {
    $this->spatialScheme = $spatialScheme;
  }

  /**
   * @return string[]
   */
  public function getSpatialValue(): array {
    return $this->spatialValue;
  }

  /**
   * @param string[] $spatialValue
   */
  public function setSpatialValue(array $spatialValue): void {
    $this->spatialValue = $spatialValue;
  }

  /**
   * @return string|null
   */
  public function getTemporalLabel(): ?string {
    return $this->temporalLabel;
  }

  /**
   * @param string|null $temporalLabel
   */
  public function setTemporalLabel(string $temporalLabel = NULL): void {
    $this->temporalLabel = $temporalLabel;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getTemporalEnd(): ?DrupalDateTime {
    return $this->formatDate($this->temporalEnd);
  }

  /**
   * @param string|null $temporalEnd
   */
  public function setTemporalEnd(string $temporalEnd = NULL): void {
    $this->temporalEnd = $temporalEnd;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getTemporalStart(): ?DrupalDateTime {
    return $this->formatDate($this->temporalStart);
  }

  /**
   * @param string|null $temporalStart
   */
  public function setTemporalStart(string $temporalStart = NULL): void {
    $this->temporalStart = $temporalStart;
  }

  /**
   * @return string|null
   */
  public function getDatasetStatus(): ?string {
    return $this->datasetStatus;
  }

  /**
   * @param string|null $datasetStatus
   */
  public function setDatasetStatus(string $datasetStatus = NULL): void {
    $this->datasetStatus = $datasetStatus;
  }

  /**
   * @return int
   */
  public function getDatasetLinkStatus(): int {
    return $this->datasetLinkStatus;
  }

  /**
   * @param int $datasetLinkStatus
   */
  public function setDatasetLinkStatus(int $datasetLinkStatus): void {
    $this->datasetLinkStatus = $datasetLinkStatus;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getDatePlanned(): ?DrupalDateTime {
    return $this->formatDate($this->datePlanned);
  }

  /**
   * @param string|null $datePlanned
   */
  public function setDatePlanned(string $datePlanned = NULL): void {
    $this->datePlanned = $datePlanned;
  }

  /**
   * @return Resource[]
   */
  public function getResources(): array {
    return $this->resources;
  }

  /**
   * @param Resource[] $resources
   */
  public function setResources(array $resources): void {
    $this->resources = $resources;
  }

  /**
   * @param bool $private
   */
  public function setPrivate(bool $private): void {
    $this->private = $private;
  }

  /**
   * @return bool
   */
  public function getPrivate(): bool {
    return $this->private;
  }

  /**
   * @param bool $highValue
   */
  public function setHighValue(bool $highValue): void {
    $this->highValue = $highValue;
  }

  /**
   * @return bool
   */
  public function getHighValue(): bool {
    return $this->highValue;
  }

  /**
   * @param bool $baseRegister
   */
  public function setBaseRegister(bool $baseRegister): void {
    $this->baseRegister = $baseRegister;
  }

  /**
   * @return bool
   */
  public function getBaseRegister(): bool {
    return $this->baseRegister;
  }

  /**
   * @param bool $referenceData
   */
  public function setReferenceData(bool $referenceData): void {
    $this->referenceData = $referenceData;
  }

  /**
   * @return bool
   */
  public function getReferenceData(): bool {
    return $this->referenceData;
  }

  /**
   * @param bool $nationalCoverage
   */
  public function setNationalCoverage(bool $nationalCoverage): void {
    $this->nationalCoverage = $nationalCoverage;
  }

  /**
   * @return bool
   */
  public function getNationalCoverage(): bool {
    return $this->nationalCoverage;
  }

  /**
   * @return string[]
   */
  public function getCommunities(): array {
    return $this->communities;
  }

  /**
   * @param string[] $communities
   */
  public function setCommunities(array $communities): void {
    $this->communities = $communities;
  }

  /**
   * @return string|null
   */
  public function getDatasetQuality(): ?string {
    return $this->datasetQuality;
  }

  /**
   * @param string|null $datasetQuality
   */
  public function setDatasetQuality(?string $datasetQuality): void {
    $this->datasetQuality = $datasetQuality;
  }

  /**
   * @return string|null
   */
  public function getCreatorUserId(): ?string {
    return $this->creatorUserId;
  }

  /**
   * @param string|null $creatorUserId
   */
  public function setCreatorUserId(?string $creatorUserId): void {
    $this->creatorUserId = $creatorUserId;
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

}
