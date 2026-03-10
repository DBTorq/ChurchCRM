<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Plugins\Catequese\CatequesePlugin;
use ChurchCRM\Slim\Middleware\Request\Auth\EditRecordsRoleAuthMiddleware;
use ChurchCRM\Slim\SlimUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\PhpRenderer;

$plugin = CatequesePlugin::getInstance();
if ($plugin === null) {
    return;
}

// MVC Routes (HTML views)
$app->group('/catequese', function (RouteCollectorProxy $group) use ($plugin): void {
    
    // Rematrícula wizard
    $group->get('/rematricula', function (Request $request, Response $response) use ($plugin): Response {
        $renderer = new PhpRenderer(__DIR__ . '/../views/');
        return $renderer->render($response, 'rematricula.php', [
            'sRootPath' => SystemURLs::getRootPath(),
            'sPageTitle' => gettext('Rematrícula Anual - Catequese'),
            'plugin' => $plugin,
        ]);
    });

    // Documentos pendentes list
    $group->get('/documentos', function (Request $request, Response $response) use ($plugin): Response {
        $documentService = $plugin->getDocumentService();
        $pending = $documentService !== null ? $documentService->getAllPendingDocuments() : [];
        
        $renderer = new PhpRenderer(__DIR__ . '/../views/');
        return $renderer->render($response, 'documentos.php', [
            'sRootPath' => SystemURLs::getRootPath(),
            'sPageTitle' => gettext('Documentos Pendentes'),
            'pending' => $pending,
        ]);
    });

    // Presença management
    $group->get('/presenca', function (Request $request, Response $response) use ($plugin): Response {
        $renderer = new PhpRenderer(__DIR__ . '/../views/');
        return $renderer->render($response, 'presenca.php', [
            'sRootPath' => SystemURLs::getRootPath(),
            'sPageTitle' => gettext('Tô Aqui Jesus - Gestão de Presença'),
            'plugin' => $plugin,
        ]);
    });

    // Ranking view
    $group->get('/ranking', function (Request $request, Response $response) use ($plugin): Response {
        $groupId = (int)($request->getQueryParams()['group_id'] ?? 0);
        $year = (int)($request->getQueryParams()['year'] ?? date('Y'));
        
        $presencaService = $plugin->getPresencaService();
        $ranking = $presencaService !== null ? $presencaService->getRanking($groupId, $year) : [];
        
        $renderer = new PhpRenderer(__DIR__ . '/../views/');
        return $renderer->render($response, 'ranking.php', [
            'sRootPath' => SystemURLs::getRootPath(),
            'sPageTitle' => gettext('Ranking - Tô Aqui Jesus'),
            'ranking' => $ranking,
            'groupId' => $groupId,
            'year' => $year,
        ]);
    });

})->add(EditRecordsRoleAuthMiddleware::class);

// API Routes (JSON)
$app->group('/catequese/api', function (RouteCollectorProxy $group) use ($plugin): void {
    
    // Rematrícula API
    $group->post('/rematricula', function (Request $request, Response $response) use ($plugin): Response {
        $data = $request->getParsedBody();
        $grupoOrigemId = (int)($data['grupo_origem_id'] ?? 0);
        $grupoDestinoId = (int)($data['grupo_destino_id'] ?? 0);
        $personIds = $data['person_ids'] ?? [];

        $matriculaService = $plugin->getMatriculaService();
        if ($matriculaService === null) {
            return SlimUtils::renderErrorJSON($response, gettext('Service not available'), [], 500);
        }

        $result = $matriculaService->migrarMembros($grupoOrigemId, $grupoDestinoId, $personIds);
        return SlimUtils::renderJSON($response, $result);
    });

    // Get groups by year
    $group->get('/grupos/{year}', function (Request $request, Response $response, array $args): Response use ($plugin) {
        $year = (int)($args['year'] ?? date('Y'));
        
        $matriculaService = $plugin->getMatriculaService();
        if ($matriculaService === null) {
            return SlimUtils::renderErrorJSON($response, gettext('Service not available'), [], 500);
        }

        $grupos = $matriculaService->getGruposPorAno($year);
        return SlimUtils::renderJSON($response, ['grupos' => $grupos]);
    });

    // Register justification
    $group->post('/attendance/{eventId}/{personId}/justify', function (
        Request $request,
        Response $response,
        array $args
    ) use ($plugin): Response {
        $eventId = (int)$args['eventId'];
        $personId = (int)$args['personId'];
        $data = $request->getParsedBody();
        $justificativa = $data['justificativa'] ?? '';
        $registradoPor = (int)($data['registrado_por'] ?? 0);

        $presencaService = $plugin->getPresencaService();
        if ($presencaService === null) {
            return SlimUtils::renderErrorJSON($response, gettext('Service not available'), [], 500);
        }

        $success = $presencaService->registrarJustificativa(
            $eventId,
            $personId,
            $justificativa,
            $registradoPor
        );

        if ($success) {
            return SlimUtils::renderJSON($response, ['success' => true]);
        }

        return SlimUtils::renderErrorJSON(
            $response,
            gettext('Não foi possível registrar a justificativa. A pessoa pode já estar presente.'),
            [],
            400
        );
    });

    // Export ranking CSV
    $group->get('/ranking/{groupId}/{year}/export', function (
        Request $request,
        Response $response,
        array $args
    ) use ($plugin): Response {
        $groupId = (int)$args['groupId'];
        $year = (int)$args['year'];

        $presencaService = $plugin->getPresencaService();
        if ($presencaService === null) {
            return SlimUtils::renderErrorJSON($response, gettext('Service not available'), [], 500);
        }

        $csv = $presencaService->exportRankingCSV($groupId, $year);
        
        $response->getBody()->write($csv);
        return $response
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', "attachment; filename=ranking_catequese_{$groupId}_{$year}.csv");
    });

})->add(EditRecordsRoleAuthMiddleware::class);
