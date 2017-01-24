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

use TwitterOAuth\Auth\ApplicationOnlyAuth;
use TwitterOAuth\Serializer\ObjectSerializer;

class rex_yfeed_stream_twitter_hashtag extends rex_yfeed_stream_abstract
{
    public function getTypeName()
    {
        return rex_i18n::msg('yfeed_twitter_hashtag');
    }

    public function getTypeParams()
    {
        return [
            [
                'label' => rex_i18n::msg('yfeed_twitter_hashtag_q'),
                'name' => 'q',
                'type' => 'string',
                'notice' => rex_i18n::msg('yfeed_twitter_hashtag_with_prefix'),
            ],
            [
                'label' => rex_i18n::msg('yfeed_twitter_count'),
                'name' => 'count',
                'type' => 'select',
                'options' => [5 => 5, 10 => 10, 15 => 15, 20 => 20, 30 => 30, 50 => 50, 75 => 75, 100 => 100],
                'default' => 10,
            ],
            [
                'label' => rex_i18n::msg('yfeed_twitter_result_type'),
                'name' => 'result_type',
                'type' => 'select',
                'options' => [
                    'mixed' => rex_i18n::msg('yfeed_twitter_result_type_mixed'),
                    'recent' => rex_i18n::msg('yfeed_twitter_result_type_recent'),
                    'popular' => rex_i18n::msg('yfeed_twitter_result_type_popular'), ],
                'default' => 'mixed',
            ],
        ];
    }

    public function fetch()
    {
        $credentials = [
            'consumer_key' => rex_config::get('yfeed', 'twitter_consumer_key'),
            'consumer_secret' => rex_config::get('yfeed', 'twitter_consumer_secret'),
            'oauth_token' => rex_config::get('yfeed', 'twitter_oauth_token'),
            'oauth_token_secret' => rex_config::get('yfeed', 'twitter_oauth_token_secret'),
        ];
        $auth = new ApplicationOnlyAuth($credentials, new ObjectSerializer());

        $params = $this->typeParams;
        $params['q'] .= ' -filter:retweets';

        $items = $auth->get('search/tweets', $params);
        $items = $items->statuses;

        foreach ($items as $twitterItem) {
            $item = new rex_yfeed_item($this->streamId, $twitterItem->id);
            $item->setContentRaw($twitterItem->text);
            $item->setContent(strip_tags($twitterItem->text));

            if (isset($twitterItem->entities->urls) && isset($twitterItem->entities->urls->url)) {
                $item->setUrl($twitterItem->entities->urls->url);
            }
            $date = new DateTime($twitterItem->created_at);
            $item->setDate($date);

            $item->setAuthor($twitterItem->user->name);
            $item->setLanguage($twitterItem->lang);
            $item->setRaw($twitterItem);
            
            $this->updateCount($item);
            $item->save();
        }
    }
}
