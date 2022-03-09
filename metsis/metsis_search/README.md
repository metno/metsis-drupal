# Metsis Search
The Metsis Search module for Drupal 8 are based on the contributed modules, search_api, search_api_solr, search_api_sorts, search_api_autocomplete and facets, for the search interface. The search interface are search_api views.

## Search API views created by METSIS search
* Metsis Search (metsis_search). The main search view. [search_api view].
* Metsis Elements (metsis_elements). Search view for child (level 2) elements. [search_api contextual view].
* Metsis Metadata Details (metsis_metadata_details). View to show additional Metadata details for a given dataset. [search_api contextual view].
* Metsis Metadata Details Parent (metsis_metadata_details_parent). View to show extended metadata information for a parent dataset. [search_api contextual view].

## Endpoints
* /metsis/search - metsis_search view endpoint.
* /metsis/elements/%/search - metsis_elements view endpoint.
* /metsis/metadata - metsis_metadata_details view endpoint.
* /metsis/metadata/parent - metsis_metadata_details_parent view endpoint.
* /metsis/search/map - Ajax callback for Search map bbox filter. (/metsis/search/map?ttlat=&tllon=&brlat&=brlon&proj=)
* /metsis/elements/count - Ajax callback for children count (/metsis/elements/count?metadata_identifier=)
* /metsis/metadata/export/{id} - Form with export options for exporting metadata given id.


## Facets
Facets for search are provided by the facets module. Facets are tied to a specific search view. Configured
facets are blocks that can be placed in regions on search page.


## TODO
* Find a way to add "Back to parent search" on child search view.
* Clean up code (remove unused code, not used configuration etc)
* Update this documentation with endpoints etc.
* Integrate result map with children (elements) view.
