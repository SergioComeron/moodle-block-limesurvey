// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript for loading LimeSurvey surveys.
 *
 * @module     block_limesurvey/surveys
 * @copyright  2024, Sergio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {

    /**
     * Load surveys with preloaded strings.
     *
     * @param {Object} $ jQuery object
     * @param {Object} contentDiv jQuery object for content div
     * @param {Object} strings Preloaded language strings
     */
    var loadSurveysWithStrings = function($, contentDiv, strings) {
        console.log('🚀 LimeSurvey block: Loading surveys...');

        Ajax.call([{
            methodname: 'block_limesurvey_get_surveys',
            args: {}
        }])[0].done(function(response) {
            console.log('✅ LimeSurvey API response received:', response);

            if (!response.success) {
                contentDiv.html('<div class="alert alert-warning">' + response.message + '</div>');
                return;
            }

            if (response.surveys.length === 0) {
                contentDiv.html('<p>' + strings.nosurveys + '</p>');
                return;
            }

            // Log all surveys data to console for debugging.
            console.group('📊 LimeSurvey - Survey Responses');
            console.log('Total surveys:', response.surveys.length);

            var html = '<div style="display: flex; flex-direction: column; gap: 10px;">';

            response.surveys.forEach(function(survey, index) {
                var extraText = survey.attributes.length > 0 ? survey.attributes.join(', ') : '';
                var surveyId = 'survey-' + index;

                // Card-style design for each survey.
                if (survey.completed) {
                    // Completed survey - gray card, not clickable.
                    html += '<div style="padding: 12px 16px; border-radius: 8px; ' +
                            'background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); ' +
                            'border-left: 4px solid #4caf50; ' +
                            'box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
                    html += '<div style="display: flex; align-items: center; justify-content: space-between;">';
                    html += '<div style="flex: 1;">';
                    html += '<div style="font-weight: 600; color: #2e7d32; margin-bottom: 4px;">' +
                            '<span style="margin-right: 8px;">✓</span>' + survey.title + '</div>';
                    if (extraText) {
                        html += '<div style="font-size: 0.85em; color: #558b2f;">' + extraText + '</div>';
                    }
                    html += '</div>';
                    html += '<span style="background: #4caf50; color: white; padding: 4px 12px; ' +
                            'border-radius: 12px; font-size: 0.75em; font-weight: 600; ' +
                            'text-transform: uppercase; letter-spacing: 0.5px;">Completed</span>';
                    html += '</div>';

                    // Add view responses button if completed and has responses.
                    if (Object.keys(survey.responses).length > 0) {
                        html += '<button class="btn btn-sm view-responses" data-survey-id="' +
                                surveyId + '" style="margin-top: 8px; padding: 6px 12px; ' +
                                'background: white; border: 1px solid #4caf50; color: #4caf50; ' +
                                'border-radius: 4px; font-size: 0.85em; cursor: pointer; ' +
                                'transition: all 0.2s;">' +
                                '📊 ' + strings.viewresponses + '</button>';
                        html += '<div id="' + surveyId + '-responses" class="survey-responses" ' +
                                'style="display: none; margin-top: 8px; padding: 12px; ' +
                                'background: white; border-radius: 4px; border: 1px solid #c8e6c9;"></div>';
                    }
                    html += '</div>';
                } else {
                    // Pending survey - blue card, clickable.
                    html += '<a href="' + survey.url + '" target="_blank" style="text-decoration: none;">';
                    html += '<div style="padding: 12px 16px; border-radius: 8px; ' +
                            'background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); ' +
                            'border-left: 4px solid #2196f3; ' +
                            'box-shadow: 0 2px 4px rgba(0,0,0,0.1); ' +
                            'transition: all 0.2s; cursor: pointer;" ' +
                            'onmouseover="this.style.boxShadow=\'0 4px 8px rgba(0,0,0,0.15)\'; ' +
                            'this.style.transform=\'translateY(-2px)\';" ' +
                            'onmouseout="this.style.boxShadow=\'0 2px 4px rgba(0,0,0,0.1)\'; ' +
                            'this.style.transform=\'translateY(0)\';">';
                    html += '<div style="display: flex; align-items: center; justify-content: space-between;">';
                    html += '<div style="flex: 1;">';
                    html += '<div style="font-weight: 600; color: #1565c0; margin-bottom: 4px;">' +
                            '<span style="margin-right: 8px;">→</span>' + survey.title + '</div>';
                    if (extraText) {
                        html += '<div style="font-size: 0.85em; color: #1976d2;">' + extraText + '</div>';
                    }
                    html += '</div>';
                    html += '<span style="background: #2196f3; color: white; padding: 4px 12px; ' +
                            'border-radius: 12px; font-size: 0.75em; font-weight: 600; ' +
                            'text-transform: uppercase; letter-spacing: 0.5px;">Pending</span>';
                    html += '</div>';
                    html += '</div>';
                    html += '</a>';
                }

                // Log survey details to console.
                console.group((index + 1) + '. ' + survey.title);
                console.log('Status:', survey.completed ? '✅ Completed' : '⬜️ Pending');
                console.log('URL:', survey.url);
                if (survey.attributes.length > 0) {
                    console.log('Attributes:', survey.attributes);
                }

                // Log API response details.
                console.group('🔍 API Response Details');
                console.log('Raw API Response:', survey.raw_api_response);
                console.log('Decoded Data:', survey.decoded_data);
                console.groupEnd();

                if (survey.completed) {
                    if (survey.responseid) {
                        console.log('Response ID:', survey.responseid);
                    }
                    if (Object.keys(survey.responses).length > 0) {
                        console.log('Responses:', survey.responses);
                    } else {
                        console.log('No response data available');
                    }
                }
                console.groupEnd();
            });

            console.groupEnd();

            html += '</div>';
            contentDiv.html(html);

            // Attach click handlers for view responses buttons.
            $('.view-responses').on('click', function(e) {
                e.preventDefault();
                var surveyId = $(this).data('survey-id');
                var responsesDiv = $('#' + surveyId + '-responses');
                var survey = response.surveys[parseInt(surveyId.split('-')[1])];
                var button = $(this);

                if (responsesDiv.is(':visible')) {
                    responsesDiv.slideUp();
                    button.html('📊 ' + strings.viewresponses);
                } else {
                    var responsesHtml = '<div style="font-weight: 600; color: #2e7d32; margin-bottom: 8px;">' +
                                       strings.responses + ':</div>';
                    responsesHtml += '<div style="display: grid; gap: 8px;">';

                    for (var key in survey.responses) {
                        if (survey.responses.hasOwnProperty(key)) {
                            responsesHtml += '<div style="padding: 8px; background: #f1f8e9; border-radius: 4px; ' +
                                           'border-left: 3px solid #4caf50;">';
                            responsesHtml += '<div style="font-size: 0.8em; color: #558b2f; margin-bottom: 2px;">' +
                                           key + '</div>';
                            responsesHtml += '<div style="color: #2e7d32; font-weight: 500;">' +
                                           survey.responses[key] + '</div>';
                            responsesHtml += '</div>';
                        }
                    }

                    responsesHtml += '</div>';
                    responsesDiv.html(responsesHtml);
                    responsesDiv.slideDown();
                    button.html('📊 ' + strings.hideresponses);
                }
            });

        }).fail(function(error) {
            contentDiv.html('<div class="alert alert-danger">' + strings.error_loading + '</div>');
            Notification.exception(error);
            console.error('❌ Error loading LimeSurvey data:', error);
        });
    };

    return {
        init: function() {
            console.log('🎯 LimeSurvey block: Initializing...');
            this.loadSurveys();
        },

        loadSurveys: function() {
            console.log('📝 Getting content div...');
            var contentDiv = $('#limesurvey-content');
            console.log('Content div found:', contentDiv.length > 0);

            // Preload all required strings.
            var stringKeys = [
                {key: 'nosurveys', component: 'block_limesurvey'},
                {key: 'activesurveys', component: 'block_limesurvey'},
                {key: 'viewresponses', component: 'block_limesurvey'},
                {key: 'hideresponses', component: 'block_limesurvey'},
                {key: 'responses', component: 'block_limesurvey'},
                {key: 'error_loading', component: 'block_limesurvey'}
            ];

            console.log('📝 Loading language strings...');

            Str.get_strings(stringKeys).then(function(loadedStrings) {
                console.log('✅ Language strings loaded:', loadedStrings);

                var strings = {
                    nosurveys: loadedStrings[0],
                    activesurveys: loadedStrings[1],
                    viewresponses: loadedStrings[2],
                    hideresponses: loadedStrings[3],
                    responses: loadedStrings[4],
                    error_loading: loadedStrings[5]
                };

                // Now load surveys with strings available.
                loadSurveysWithStrings($, contentDiv, strings);
            }).catch(function(error) {
                console.error('❌ Error loading strings:', error);
                contentDiv.html('<div class="alert alert-danger">Error loading language strings</div>');
            });
        }
    };
});
