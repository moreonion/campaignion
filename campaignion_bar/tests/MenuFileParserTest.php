<?php

namespace Drupal\campaignion_bar;

class MenuFileParserTest extends \DrupalUnitTestCase {
  public function testParserWorks() {
    $start = time();
    // import menu
    $menu_name = 'ae-menu';
    $uri = DRUPAL_ROOT . '/' . drupal_get_path('module', 'campaignion_bar') . '/' . $menu_name . '.txt';
    $parser = new MenuFileParser($menu_name);
    $menu = $parser->parseFile($uri);
    $this->assertTrue($menu instanceof MenuItem);
    $this->assertTrue(time() - $start  < 3, "Parsing menu files isn't terribly slow.");
  }

  public function testToMenuLinks() {
    $menu_name = 'ae-menu';
    $uri = DRUPAL_ROOT . '/' . drupal_get_path('module', 'campaignion_bar') . '/' . $menu_name . '.txt';
    $parser = new MenuFileParser($menu_name);
    $menu = $parser->fileToMenuLinks($uri);
    foreach ($menu as $key => $item) {
      $must_have = array('mlid', 'plid', 'router_path', 'hidden', 'external', 'expanded', 'weight', 'depth', 'link_title', 'options', 'customized', 'title_callback');
      foreach ($must_have as $key) {
        $this->assertTrue(isset($item[$key]), "'$key' is set for all menu items");
      }
    }
  }
}
