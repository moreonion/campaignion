<?php

namespace Drupal\campaignion_source_tags;

use Drupal\campaignion\CRM\ImporterBase;
use Drupal\campaignion\CRM\Import\Field\Name;
use Drupal\campaignion\CRM\Import\Source\ArraySource;
use Drupal\campaignion_supporter_tags\Tagger;

/**
 * Test for setting source tags.
 */
class SourceTagTest extends \DrupalWebTestCase {

  /**
   * Test setting the source tag during an import.
   */
  public function testSetSourceTagDuringImport() {
    $mappings = [
      new Name('first_name', 'first_name'),
      new Name('last_name', 'last_name'),
    ];
    $importer = new ImporterBase($mappings);
    $tagger = Tagger::byNameAndParentUuid('supporter_tags', '0fd2977e-9927-4de7-b4c2-e0bde71fc605');
    $source = new ArraySource([
      'first_name' => 'F',
      'last_name' => 'L',
      'email' => 'testsetsourcetagduringimport@example.com',
    ]);
    $contact = $importer->findOrCreateContact($source);
    $tagger->tag($contact->supporter_tags, ['test-source'], TRUE);
    $importer->import($source, $contact);
    $contact->save();
    $this->assertNotEmpty($contact->source_tag[LANGUAGE_NONE][0]);
    entity_delete('redhen_contact', $contact->contact_id);
  }

}
