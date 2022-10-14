<?php

namespace markhuot\craftpest\console;

use craft\console\Controller;

class IdeController extends Controller
{
    function actionGenerateFieldStubs()
    {

        $contents = \Craft::$app->view->renderTemplate('pest/compiled_classes/entry.twig', []);
        file_put_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'storage', 'EntryFactory.php']), $contents);

        $sections = \Craft::$app->sections->getAllSections();
        foreach ($sections as $section) {
            $sectionClassName = 'Section' . ucfirst($section->handle);
            $namespace = ['markhuot', 'craftpest', $sectionClassName];
            $dirName = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'storage', $sectionClassName]);
            if (!is_dir($dirName)) {
                mkdir($dirName, 0700, true);
            }

            foreach ($section->entryTypes as $entryType) {
                $entryTypeClassName = 'EntryType' . ucfirst($entryType->handle);
                $contents = \Craft::$app->view->renderTemplate('pest/compiled_classes/entry-type.twig', [
                    'section' => $section,
                    'entryType' => $entryType,
                ]);
                file_put_contents(implode(DIRECTORY_SEPARATOR, [$dirName, $entryTypeClassName . '.php']), $contents);
            }
        }
    }
}