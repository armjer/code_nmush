public function getoneAction()
    {
        $config = Zend_Registry::get('config');
        B_JsContainer::addData('baseUrl', $config->site->frontend->url);
        $id = $this->getParam('id');
        if (!$id) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $container = $this->initLayout('main');

        $collectorLocator = $this->getCollectorLocator();
        $collector = new Frontend_LayoutResources_Collector_Institution_Current(
            $collectorLocator->createCurrentInstitutionCollector()
        );

        $collector->collectTo($container);
        $institution = $container->get(Frontend_LayoutResources_Collector_Institution_Current::INSTITUTION_CURRENT);
        if (!$institution) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $mapCollector = new Frontend_LayoutResources_Collector_MapMarkers(
            [$institution], $collectorLocator->createServerUrlCollector()
        );
        $mapCollector->collectTo($container);

        $instImagesCollector = $collectorLocator->createInstitutionImagesCollector();
        $instImagesCollector->collectTo($container);

        $userBookmarksCollector = $collectorLocator->createRegisteredUserBookmarksCollector();
        $userBookmarksCollector->collectTo($container);

        $activeTab = 'reviews';
        switch ($this->getParam('tab')) {
            case 'reviews':
                $activeTab = 'reviews';
                break;
            case 'specials':
                $activeTab = 'specials';
                break;
            case 'events':
                $activeTab = 'events';
                break;
        }

        switch ($this->getParam('event')) {
            case 'reviews':
                $activeTab = 'reviews';
                break;
            case 'specials':
                $activeTab = 'specials';
                break;
            case 'events':
                $activeTab = 'events';
                break;
        }


        $container->addResource('activeTab', $activeTab);
        $container->addResource('limit', $this->getParam('limit') ? $this->getParam('limit') : 3);

        $advertisingCollector = new Frontend_LayoutResources_Collector_Institution_Advertising($collector);
        $advertisingCollector->collectTo($container);

        $reviewsCollector = new Frontend_LayoutResources_Collector_Activity_Institution($this->getRequest());
        $reviewsCollector->setRegisteredUserDataProvider(new Frontend_User_RegUserContainerDataProvider(
            $collectorLocator->createRegisteredUserCollector()
        ));
        $reviewsCollector->collectTo($container);

        $lastDiscussCollector = $collectorLocator->createLastDiscussionsCollector();
        $lastDiscussCollector->collectTo($container);

        $newInstitutionsCollector = $collectorLocator->createNewInstitutionsCollector();
        $newInstitutionsCollector->collectTo($container);

        $specialsCollector = new Frontend_LayoutResources_Collector_Institution_Specials(
            $collectorLocator->createLastDiscussionsCollector()
        );
        $specialsCollector->setRegisteredUserDataProvider(new Frontend_User_RegUserContainerDataProvider(
            $collectorLocator->createRegisteredUserCollector()
        ));
        $specialsCollector->collectTo($container);

        $eventsCollector = new Frontend_LayoutResources_Collector_Institution_Events(
            $collectorLocator->createGeoLocationCollector(), $collectorLocator->createLastDiscussionsCollector()
        );
        $eventsCollector->collectTo($container);

        $eventsModel = Site_Model_Events::getInstance();
        $eventsInst = $eventsModel->getEventsInstitution($institution['city_id'], null, $institution['id']);

        if (count($eventsInst)) {
            $limit = $this->getParam('limit', 3);
            if (count($eventsInst) > $limit) {
                $this->view->nextLimit = $limit + 5;
            }
        }

        /*  get following count  */
        $foolwingModel = new Site_Model_Following();
        $reviwes = $container->get(Frontend_LayoutResources_ResourceDefinitions::INSTITUTION_REVIEWS);
        foreach($reviwes as &$reviwe) {
            $reviwe['followingCount'] = $foolwingModel->getUserFollowingCount($reviwe['user_id']);
        }
        $container->addResource(Frontend_LayoutResources_ResourceDefinitions::INSTITUTION_REVIEWS, $reviwes);

        /*   get evetnts comments  */
        $commentObj = Site_Model_Event_Comments::getInstance();
        $eventsInstitution   = $container->get(Frontend_LayoutResources_ResourceDefinitions::EVENTS_INSTITUTION);
        $comments = Array();
        foreach($eventsInstitution as $event) {
            $comment = $commentObj->getInstance()->getByEventId($event['id']);
            $comments[$event['id']][] = $comment;
        }
        $container->addResource(Frontend_LayoutResources_ResourceDefinitions::INSTITUTION_COMMENT, $comments);

        $this->assignContainerToView($container);
    }

    /**
     * Get more Ajax
     * @throws Exception
     */
    public function getMoreAction()
    {
        $this->disableLayout();
        $this->disableViewRendering();

        $type = $this->getParam('type', 'reviews');
        switch ($type) {
            case 'reviews':
                $this->getMoreReviews();
                break;
            case 'subcategories':
                $this->getMoreSubcategories();
                break;
            default:
                $this->returnJson(['success' => false, 'message' => 'Not implemented']);
        }
    }
