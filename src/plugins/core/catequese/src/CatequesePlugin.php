<?php

namespace ChurchCRM\Plugins\Catequese;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\Config\Menu\MenuItem;
use ChurchCRM\Plugin\AbstractPlugin;
use ChurchCRM\Plugin\Hook\HookManager;
use ChurchCRM\Plugin\Hooks;
use ChurchCRM\Plugins\Catequese\Service\DocumentService;
use ChurchCRM\Plugins\Catequese\Service\MatriculaService;
use ChurchCRM\Plugins\Catequese\Service\PresencaService;

class CatequesePlugin extends AbstractPlugin
{
    private static ?CatequesePlugin $instance = null;
    private ?DocumentService $documentService = null;
    private ?MatriculaService $matriculaService = null;
    private ?PresencaService $presencaService = null;

    public function __construct(string $basePath = '')
    {
        parent::__construct($basePath);
        self::$instance = $this;
    }

    public static function getInstance(): ?CatequesePlugin
    {
        return self::$instance;
    }

    public function getId(): string
    {
        return 'catequese';
    }

    public function getName(): string
    {
        return gettext('Módulo de Catequese');
    }

    public function getDescription(): string
    {
        return gettext('Sistema completo para gestão de catequese com rematrícula, documentos, presença e ranking.');
    }

    public function boot(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Initialize services
        $this->documentService = new DocumentService();
        $this->matriculaService = new MatriculaService();
        $this->presencaService = new PresencaService();

        // Register hooks
        HookManager::addFilter(Hooks::PERSON_VIEW_TABS, [$this, 'addPersonViewTabs'], 10);
        HookManager::addFilter(Hooks::DASHBOARD_WIDGETS, [$this, 'addDashboardWidgets'], 10);
        HookManager::addAction(Hooks::EVENT_CHECKIN, [$this, 'onEventCheckin'], 10);
        HookManager::addAction(Hooks::PERSON_UPDATED, [$this, 'onPersonUpdated'], 10);
        HookManager::addFilter(Hooks::MENU_BUILDING, [$this, 'addCatequeseMenu'], 10);
    }

    /**
     * Add tabs to person profile view
     */
    public function addPersonViewTabs(array $tabs): array
    {
        if (!$this->isEnabled() || $this->documentService === null) {
            return $tabs;
        }

        $tabs['catequese_docs'] = [
            'title' => gettext('Documentos'),
            'content' => $this->documentService->renderPersonDocumentTab($_GET['PersonID'] ?? 0),
        ];

        return $tabs;
    }

    /**
     * Add dashboard widgets
     */
    public function addDashboardWidgets(array $widgets): array
    {
        if (!$this->isEnabled() || $this->documentService === null) {
            return $widgets;
        }

        $user = AuthenticationManager::getCurrentUser();
        if ($user->isAdmin() || $user->isEditRecordsEnabled()) {
            $widgets['catequese_pending_docs'] = [
                'title' => gettext('Documentos Pendentes - Catequese'),
                'content' => $this->documentService->renderDashboardWidget(),
                'order' => 50,
            ];
        }

        return $widgets;
    }

    /**
     * Handle event check-in to update ranking
     */
    public function onEventCheckin($eventId, $personId, $checkinData): void
    {
        if (!$this->isEnabled() || $this->presencaService === null) {
            return;
        }

        $this->presencaService->registrarPresenca((int)$eventId, (int)$personId);
    }

    /**
     * Validate responsável when person is updated
     */
    public function onPersonUpdated($person, array $oldData): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Validation logic will be implemented in Service layer
    }

    /**
     * Add Catequese menu to navigation
     */
    public function addCatequeseMenu(array $menus): array
    {
        if (!$this->isEnabled()) {
            return $menus;
        }

        $user = AuthenticationManager::getCurrentUser();
        if (!$user->isEditRecordsEnabled() && !$user->isAdmin()) {
            return $menus;
        }

        $menu = new MenuItem(gettext('Catequese'), '', true, 'fa-book-open');
        
        $menu->addSubMenu(new MenuItem(
            gettext('Rematrícula'),
            'plugins/catequese/rematricula',
            true,
            'fa-user-graduate'
        ));
        
        $menu->addSubMenu(new MenuItem(
            gettext('Documentos Pendentes'),
            'plugins/catequese/documentos',
            true,
            'fa-file-alt'
        ));
        
        $menu->addSubMenu(new MenuItem(
            gettext('Tô Aqui Jesus - Presença'),
            'plugins/catequese/presenca',
            true,
            'fa-check-circle'
        ));
        
        $menu->addSubMenu(new MenuItem(
            gettext('Ranking'),
            'plugins/catequese/ranking',
            true,
            'fa-trophy'
        ));

        $menus['Catequese'] = $menu;

        return $menus;
    }

    public function activate(): void
    {
        // Migration will be run separately
    }

    public function deactivate(): void
    {
        // Keep data when deactivated
    }

    public function uninstall(): void
    {
        // Preserve data - admin can manually clean up if needed
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function getConfigurationError(): ?string
    {
        return null;
    }

    public function getDocumentService(): ?DocumentService
    {
        return $this->documentService;
    }

    public function getMatriculaService(): ?MatriculaService
    {
        return $this->matriculaService;
    }

    public function getPresencaService(): ?PresencaService
    {
        return $this->presencaService;
    }
}
