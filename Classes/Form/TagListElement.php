<?php
declare(strict_types=1);
namespace B13\Tag\Form;

/*
 * This file is part of TYPO3 CMS-based extension "tag" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Render a list of tags.
 */
class TagListElement extends AbstractFormElement
{
    public function render()
    {
        $resultArray = $this->initializeResultArray();
        $selectedItems = $this->data['parameterArray']['itemFormElValue'] ?? [];
        $elementName = $this->data['parameterArray']['itemFormElName'];

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldControlResult = $this->renderFieldControl();
        $fieldControlHtml = $fieldControlResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldControlResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $tagsId = StringUtility::getUniqueId('formengine-tags-');

        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] =   $fieldInformationHtml;
        $html[] =   '<div class="form-control-wrap">';
        $html[] =       '<div class="form-wizards-wrap">';
        $html[] =           '<div class="form-wizards-element">';
        $html[] =               '<input type="hidden" name="' . htmlspecialchars($elementName) . '[] " value="">';
        $html[] =               '<select multiple class="form-control" name="' . htmlspecialchars($elementName) . '[]" id="' . $tagsId . '" placeholder="Add Keywords by typing">';
        $html[] =               '</select>';
        $html[] =           '</div>';
        if (!empty($fieldControlHtml)) {
            $html[] =           '<div class="form-wizards-items-aside">';
            $html[] =               '<div class="btn-group">';
            $html[] =                   $fieldControlHtml;
            $html[] =               '</div>';
            $html[] =           '</div>';
        }
        if (!empty($fieldWizardHtml)) {
            $html[] = '<div class="form-wizards-items-bottom">';
            $html[] = $fieldWizardHtml;
            $html[] = '</div>';
        }
        $html[] =       '</div>';
        $html[] =   '</div>';
        $html[] = '</div>';

        $items = [];
        foreach ($selectedItems as $itemValue) {
            if (empty($itemValue)) {
                continue;
            }
            $tagRecord = BackendUtility::getRecord('sys_tag', $itemValue);
            if (!is_array($tagRecord)) {
                continue;
            }
            $items[] = [
                'value' => (int)$itemValue,
                'name' => htmlspecialchars($tagRecord['name']),
            ];
        }

        $ajaxUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('ajax_tag_suggest_tags');
        $resultArray['html'] = implode(LF, $html);

        $resultArray['stylesheetFiles'][] = 'EXT:tag/Resources/Public/StyleSheets/tagsinput.css';
        $resultArray['requireJsModules'][] = [
            'TYPO3/CMS/Tag/TagsInputElement' => 'function(TagsInputElement) {
                new TagsInputElement("' . $tagsId . '", {
                    itemValue: function(item) {
                        return item.value || item;
                    },
                    itemText: function(item) {
                        return item.name || item;
                    },
                    items: ' . json_encode($items) . ',
                    typeahead: {
                        minLength: 2,
                        source: function(query) {
                            var url = ' . GeneralUtility::quoteJSvalue($ajaxUrl) . ' + "&q=" + query;
                            return $.getJSON(url);
                        }
                    }
                });
            }'
        ];
        return $resultArray;
    }
}