<?php
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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

/**
 * Behat steps for filter_panoptoltibutton.
 *
 * @package    filter_panoptoltibutton
 * @category   test
 * @copyright  2026 onwards University College London {@link https://www.ucl.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_filter_panoptoltibutton extends behat_base {
    /**
     * Inserts videos through the open Panopto dialogue, as if Panopto had returned them.
     *
     * @When /^I insert the following Panopto videos:$/
     * @param TableNode $table Videos to insert, with a title column.
     */
    public function i_insert_the_following_panopto_videos(TableNode $table): void {
        // Build the fake videos as LTI content items, the format Panopto returns.
        $items = [];
        foreach ($table->getHash() as $row) {
            // Turn each table row into one video.
            $items[] = [
                'mediaType' => 'application/vnd.ims.lti.v1.ltilink',
                'title' => $row['title'],
                'custom' => ['video' => $row['title']],
                // Make the Tiny plugin use iframe.
                'placementAdvice' => [
                    'presentationDocumentTarget' => 'iframe',
                    'displayWidth' => 640,
                    'displayHeight' => 360,
                ],
            ];
        }
        $contentitems = json_encode(['@graph' => $items]);

        // Call the dialogue's callback once the dialogue is ready.
        $script = <<<JS
        (() => {
            // The Panopto dialogue (an iframe containing panoptowrapper.html).
            const dialogue = document.querySelector('iframe[src*="panoptowrapper.html"]')?.contentWindow;
            // Wait until the inner frame has left Moodle for the fake Panopto server: by then it has
            // loaded, so editor.js is capturing titles (this always happens for a real user).
            if (dialogue?.document.getElementById('pageframe')?.contentDocument !== null) {
                return false;
            }

            // Panopto sends its results to a function in document.CALLBACKS.
            const callbacks = dialogue.document.CALLBACKS ?? {};
            const name = Object.keys(callbacks).find((key) => key.startsWith('f'));
            if (!name) {
                return false;
            }
            // Call using fake videos.
            callbacks[name]({$contentitems});
            return true;
        })()
        JS;

        // Keep trying until successful.
        $this->spin(
            fn() => $this->evaluate_script($script),
            exception: new ExpectationException('The Panopto dialogue did not finish loading', $this->getSession()),
        );
    }
}
