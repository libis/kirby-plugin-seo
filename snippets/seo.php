<?php

$title = $page->seoTitle();
$description = $page->seo_description() != "" ? $page->seo_description() : $site->site_meta_description();
$robots = $page->seoRobots();
$canonical = $page->seoCanonical();

$og = [
  'title' => $page->og_title()->or($page->seoTitle())->value(),
  'description' => $page->og_description()->or($site->site_meta_description())->value(),
  'image' => $page->og_image()->toFile()
      ? $page->og_image()->toFile()->url()
      : ($site->og_default_image()->toFile()
          ? $site->og_default_image()->toFile()->url()
          : ''),
  'url' => $page->url(),
  'site_name' => $site->og_site_name()->value(),
  'type' => 'website',
];
?>

  <?php if ($title): ?>
  <title><?= esc($title) ?></title>
  <?php endif ?>

  <?php if ($description): ?>
  <meta name="description" content="<?= esc($description) ?>">
  <?php endif ?>

    <meta name="robots" content="<?= esc($robots) ?>">

  <?php if ($canonical != ""): ?>
  <link rel="canonical" href="<?= esc($canonical) ?>" />
  <?php endif ?>

    <meta property="og:title" content="<?= esc($og['title'] ?? '') ?>">
    <meta property="og:description" content="<?= esc($og['description'] ?? '') ?>">
    <meta property="og:url" content="<?= esc($og['url'] ?? '') ?>">
    <meta property="og:site_name" content="<?= esc($og['site_name'] ?? '') ?>">
    <meta property="og:type" content="<?= esc($og['type'] ?? '') ?>">

  <?php if ($og['image']): ?>
    <meta property="og:image" content="<?= esc($og['image'] ?? '') ?>">
  <?php endif ?>

    <meta name="twitter:card" content="summary_large_image">

    <?php snippet('seo-jsonld') ?>