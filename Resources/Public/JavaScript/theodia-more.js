/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-theodia-show-more]').forEach(function (element) {
        element.addEventListener('click', function (event) {
            event.preventDefault();
            const url = element.dataset.theodiaUrl;
            const plugin = element.dataset.theodiaPlugin;
            const targetEl = document.getElementById(element.dataset.theodiaTarget);

            var formData = new FormData();
            formData.append('plugin', plugin);
            formData.append('offset', targetEl.dataset.events);

            fetch(url, {
                method: 'POST',
                mode: 'same-origin',
                cache: 'no-cache',
                credentials: 'same-origin',
                referrerPolicy: 'no-referrer',
                body: formData
            }).then(response => response.json())
                .then(data => {
                    console.log(data);
                });
        });
    });
});
