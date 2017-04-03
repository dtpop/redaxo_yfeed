<?php

/**
 * This file is part of the YFeed package.
 *
 * @author (c) Yakamara Media GmbH & Co. KG
 * @author thomas.blum@redaxo.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (rex_addon::get('cronjob')->isAvailable()) {
    rex_cronjob_manager::registerType(rex_cronjob_yfeed::class);
}

if (rex_addon::get('media_manager')->isAvailable()) {
    rex_media_manager::addEffect(rex_effect_yfeed::class);
}
