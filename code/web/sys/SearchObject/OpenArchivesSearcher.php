<?php
require_once ROOT_DIR . '/sys/SearchObject/SolrSearcher.php';
class SearchObject_OpenArchivesSearcher extends SearchObject_SolrSearcher
{
    public function __construct(){
        parent::__construct();

        global $configArray;
        global $timer;

        $this->resultsModule = 'OpenArchives';

        $this->searchType = 'open_archives';
        $this->basicSearchType = 'open_archives';

        require_once ROOT_DIR . "/sys/SolrConnector/OpenArchivesSolrConnector.php";
        $this->indexEngine = new OpenArchivesSolrConnector($configArray['Index']['url']);
        $timer->logTime('Created Index Engine for Open Archives');

        $this->allFacetSettings = getExtraConfigArray('openArchivesFacets');
        $facetLimit = $this->getFacetSetting('Results_Settings', 'facet_limit');
        if (is_numeric($facetLimit)) {
            $this->facetLimit = $facetLimit;
        }
        $translatedFacets = $this->getFacetSetting('Advanced_Settings', 'translated_facets');
        if (is_array($translatedFacets)) {
            $this->translatedFacets = $translatedFacets;
        }

        // Load search preferences:
        $searchSettings = getExtraConfigArray('openArchivesSearches');
        $this->defaultIndex = 'OpenArchivesKeyword';
        if (isset($searchSettings['General']['default_sort'])) {
            $this->defaultSort = $searchSettings['General']['default_sort'];
        }
        if (isset($searchSettings['DefaultSortingByType']) &&
            is_array($searchSettings['DefaultSortingByType'])) {
            $this->defaultSortByType = $searchSettings['DefaultSortingByType'];
        }
        if (isset($searchSettings['Basic_Searches'])) {
            $this->searchIndexes = $searchSettings['Basic_Searches'];
        }
        if (isset($searchSettings['Advanced_Searches'])) {
            $this->advancedTypes = $searchSettings['Advanced_Searches'];
        }

        // Load sort preferences (or defaults if none in .ini file):
        if (isset($searchSettings['Sorting'])) {
            $this->sortOptions = $searchSettings['Sorting'];
        } else {
            $this->sortOptions = array('relevance' => 'sort_relevance',
                'year' => 'sort_year', 'year asc' => 'sort_year asc',
                'title' => 'sort_title');
        }

        // Debugging
        $this->indexEngine->debug = $this->debug;
        $this->indexEngine->debugSolrQuery = $this->debugSolrQuery;

        $timer->logTime('Setup Open Archives Search Object');
    }

    /**
     * Initialise the object from the global
     *  search parameters in $_REQUEST.
     *
     * @access  public
     * @return  boolean
     */
    public function init($searchSource = null)
    {
        // Call the standard initialization routine in the parent:
        parent::init('open_archives');

        //********************
        // Check if we have a saved search to restore -- if restored successfully,
        // our work here is done; if there is an error, we should report failure;
        // if restoreSavedSearch returns false, we should proceed as normal.
        $restored = $this->restoreSavedSearch();
        if ($restored === true) {
            return true;
        } else if ($restored instanceof AspenError) {
            return false;
        }

        //********************
        // Initialize standard search parameters
        $this->initView();
        $this->initPage();
        $this->initSort();
        $this->initFilters();

        //********************
        // Basic Search logic
        if ($this->initBasicSearch()) {
            // If we found a basic search, we don't need to do anything further.
        } else {
            $this->initAdvancedSearch();
        }

        // If a query override has been specified, log it here
        if (isset($_REQUEST['q'])) {
            $this->query = $_REQUEST['q'];
        }

        return true;
    } // End init()

    public function getSearchIndexes()
    {
        return [
            'OpenArchivesKeyword' => 'Keyword',
            'OpenArchivesTitle' => 'Title',
            'OpenArchivesSubject' => 'Subject',
        ];
    }

    /**
     * Turn our results into an Excel document
     * @param null|array $result
     */
    public function buildExcel($result = null)
    {
        // TODO: Implement buildExcel() method.
    }

    public function getUniqueField(){
        return 'identifier';
    }

    public function getRecordDriverForResult($current)
    {
        require_once ROOT_DIR . '/RecordDrivers/OpenArchivesRecordDriver.php';
        return new OpenArchivesRecordDriver($current);
    }

    public function getSearchesFile()
    {
        return 'openArchivesSearches';
    }

    public function supportsSuggestions()
    {
        return true;
    }

    /**
     * @param string $searchTerm
     * @param string $searchIndex
     * @return array
     */
    public function getSearchSuggestions($searchTerm, $searchIndex){
        $suggestionHandler = 'suggest';
        if ($searchIndex == 'OpenArchivesTitle') {
            $suggestionHandler = 'title_suggest';
        }if ($searchIndex == 'OpenArchivesSubject') {
            $suggestionHandler = 'subject_suggest';
        }
        return $this->processSearchSuggestions($searchTerm, $suggestionHandler);
    }
}