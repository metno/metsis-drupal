## METSIS Basket Module
Author: Magnar Martinsen, magnarem@met.no

This module provide an endpoint for adding mmd-products to the basket from the search interface.
It also contains a VBO view for showing the products added to basket with the posibility to delete added products.

## Endpoint
The following URI endpoints are provided by this module:
* /metsis/mybasket -> Show your basket with the items added.
* /metsis/basket/add/{id} -> Ajax endpoint which add the product with current {id} to the basket.

