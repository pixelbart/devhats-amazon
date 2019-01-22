# Help

You can use two simple functions to save date into your database and retrieve it.

## Retriving data from database (array)
Can be used to retrieve informations from products you saved with `devhats_amazon_get_product();`.

```
$asin = 'Product ASIN';
devhats_amazon_get_product($asin);
```

## Saving data into your database
Can be used in a wordpress filter for saving meta box information (asin inside your posts) into database.

```
$asin = 'YOUR_ASIN';
devhats_amazon_update_product($asin);
```
