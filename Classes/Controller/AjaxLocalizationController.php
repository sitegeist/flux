<?php
namespace FluidTYPO3\Flux\Controller;

use FluidTYPO3\Flux\Service\ContentService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\Page\LocalizationController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;


class AjaxLocalizationController extends LocalizationController
{
    public function getRecordLocalizeSummary(ServerRequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getQueryParams();
        if ($params['colPos'] != ContentService::COLPOS_FLUXCONTENT) {
            return parent::getRecordLocalizeSummary($request, $response);
        }

        if (!isset($params['colPos'])) {
            $response = $response->withStatus(500);
            return $response;
        }

        $recordIds = $params['elementIds'];
        $records = [];
        $databaseConnection = $this->getDatabaseConnection();
        $res = $databaseConnection->exec_SELECTquery('*', 'tt_content', 'uid IN (' . implode(',', $recordIds) . ')');
        while ($row = $databaseConnection->sql_fetch_assoc($res)) {
            $records[] = [
                'icon' => $this->iconFactory->getIconForRecord('tt_content', $row, Icon::SIZE_SMALL)->render(),
                'title' => $row[$GLOBALS['TCA']['tt_content']['ctrl']['label']],
                'uid' => $row['uid']
            ];
        }
        $databaseConnection->sql_free_result($res);

        $response->getBody()->write(json_encode($records));
        return $response;
    }

}
