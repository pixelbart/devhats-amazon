<?php

function devhats_amazon_get_product($asin) {
  $amazon = new DevhatsAmazon\Amazon;
  return $amazon->select_product($asin);
}

function devhats_amazon_update_product($asin) {
  $amazon = new DevhatsAmazon\Amazon;
  return $amazon->update_product($asin);
}
