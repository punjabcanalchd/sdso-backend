<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $translations = [
            // Header
            [
                'group' => 1,
                'key' => 'Header.HeadLine',
                'en' => 'State Dam Safety Organization (SDSO) | Dam Safety Organisation',
                'pb' => 'ਰਾਜ ਡੈਮ ਸੁਰੱਖਿਆ ਸੰਗਠਨ (SDSO) | ਡੈਮ ਸੁਰੱਖਿਆ ਸੰਗਠਨ'
            ],
            [
                'group' => 1,
                'key' => 'Header.Login',
                'en' => 'Login',
                'pb' => 'ਲਾਗਇਨ'
            ],
            [
                'group' => 1,
                'key' => 'Header.Dashboard',
                'en' => 'Dashboard',
                'pb' => 'ਡੈਸ਼ਬੋਰਡ'
            ],
            [
                'group' => 1,
                'key' => 'Header.Logout',
                'en' => 'Logout',
                'pb' => 'ਲਾਗਆਉਟ'
            ],
            [
                'group' => 1,
                'key' => 'key_login',
                'en' => 'Login',
                'pb' => 'ਲਾਗਇਨ'
            ],
            [
                'group' => 1,
                'key' => 'key_dashboard',
                'en' => 'Dashboard',
                'pb' => 'ਡੈਸ਼ਬੋਰਡ'
            ],
            [
                'group' => 1,
                'key' => 'key_logout',
                'en' => 'Logout',
                'pb' => 'ਲਾਗਆਉਟ'
            ],
            // Noticeboard
            [
                'group' => 2,
                'key' => 'NOTICEBOARD.TITLE',
                'en' => 'Noticeboard',
                'pb' => 'ਨੋਟਿਸ ਬੋਰਡ'
            ],
            [
                'group' => 2,
                'key' => 'NOTICEBOARD.SERIAL_NO',
                'en' => 'Sr. No.',
                'pb' => 'ਲੜੀ ਨੰ.'
            ],
            [
                'group' => 2,
                'key' => 'NOTICEBOARD.DESCRIPTION',
                'en' => 'Description',
                'pb' => 'ਵੇਰਵਾ'
            ],
            [
                'group' => 2,
                'key' => 'NOTICEBOARD.PUBLISH_DATE',
                'en' => 'Publish Date',
                'pb' => 'ਪ੍ਰਕਾਸ਼ਨ ਮਿਤੀ'
            ],
            [
                'group' => 2,
                'key' => 'NOTICEBOARD.DOWNLOAD',
                'en' => 'Download',
                'pb' => 'ਡਾਊਨਲੋਡ'
            ],
            [
                'group' => 2,
                'key' => 'NOTICEBOARD.NO_RECORDS',
                'en' => 'No records found',
                'pb' => 'ਕੋਈ ਰਿਕਾਰਡ ਨਹੀਂ ਮਿਲਿਆ'
            ],
            // Ticker
            [
                'group' => 2,
                'key' => 'TICKER.ATTENTION',
                'en' => 'Attention:',
                'pb' => 'ਧਿਆਨ ਦਿਓ:'
            ],
            [
                'group' => 2,
                'key' => 'TICKER.DEFAULT_MESSAGE',
                'en' => 'Welcome to State Dam Safety Organization (SDSO) Official Portal.',
                'pb' => 'ਸਟੇਟ ਡੈਮ ਸੇਫਟੀ ਆਰਗੇਨਾਈਜ਼ੇਸ਼ਨ (SDSO) ਦੇ ਅਧਿਕਾਰਤ ਪੋਰਟਲ \'ਤੇ ਤੁਹਾਡਾ ਸਵਾਗਤ ਹੈ।'
            ],
            // Work Gallery
            [
                'group' => 2,
                'key' => 'WORK_GALLERY.TITLE',
                'en' => 'Work Gallery',
                'pb' => 'ਵਰਕ ਗੈਲਰੀ'
            ],
            [
                'group' => 2,
                'key' => 'WORK_GALLERY.VIEW_ALL',
                'en' => 'View All',
                'pb' => 'ਸਾਰੇ ਦੇਖੋ'
            ],
            [
                'group' => 2,
                'key' => 'WORK_GALLERY.IMAGE_COMING_SOON',
                'en' => 'Image Coming Soon',
                'pb' => 'ਤਸਵੀਰ ਜਲਦੀ ਆ ਰਹੀ ਹੈ'
            ],
            [
                'group' => 2,
                'key' => 'WORK_GALLERY.SHOWING',
                'en' => 'Showing',
                'pb' => 'ਦਿਖਾ ਰਿਹਾ ਹੈ'
            ],
            [
                'group' => 2,
                'key' => 'WORK_GALLERY.OF',
                'en' => 'of',
                'pb' => 'ਵਿੱਚੋਂ'
            ],
            [
                'group' => 2,
                'key' => 'WORK_GALLERY.NO_ITEMS',
                'en' => 'No gallery items available at this time.',
                'pb' => 'ਇਸ ਸਮੇਂ ਕੋਈ ਗੈਲਰੀ ਆਈਟਮ ਉਪਲਬਧ ਨਹੀਂ ਹੈ।'
            ],
            // Footer
            [
                'group' => 1,
                'key' => 'FOOTER.POLICIES_AND_HELP',
                'en' => 'Policies & Help',
                'pb' => 'ਨੀਤੀਆਂ ਅਤੇ ਸਹਾਇਤਾ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.SITE_STATISTICS',
                'en' => 'Site Statistics',
                'pb' => 'ਸਾਈਟ ਅੰਕੜੇ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.TOTAL_VISITORS',
                'en' => 'Total Visitors',
                'pb' => 'ਕੁੱਲ ਵਿਜ਼ਿਟਰ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.LAST_UPDATED',
                'en' => 'Last Updated',
                'pb' => 'ਆਖਰੀ ਅੱਪਡੇਟ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.ALL_RIGHTS_RESERVED',
                'en' => 'All Rights Reserved',
                'pb' => 'ਸਾਰੇ ਹੱਕ ਰਾਖਵੇਂ ਹਨ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.PRIVACY_POLICY',
                'en' => 'Privacy Policy',
                'pb' => 'ਪ੍ਰਾਈਵੇਸੀ ਪਾਲਿਸੀ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.TERMS_OF_USE',
                'en' => 'Terms of Use',
                'pb' => 'ਵਰਤੋਂ ਦੀਆਂ ਸ਼ਰਤਾਂ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.ACCESSIBILITY',
                'en' => 'Accessibility',
                'pb' => 'ਪਹੁੰਚਯੋਗਤਾ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.SITEMAP',
                'en' => 'Sitemap',
                'pb' => 'ਸਾਈਟਮੈਪ'
            ],
            [
                'group' => 1,
                'key' => 'FOOTER.FOLLOWS',
                'en' => 'Follows',
                'pb' => 'ਪਾਲਣਾ ਕਰਦਾ ਹੈ'
            ]
        ];

        foreach ($translations as $item) {
            $record = DB::table('translations')->where('translation_key', $item['key'])->first();
            if (!$record) {
                $keyId = DB::table('translations')->insertGetId([
                    'group'           => $item['group'],
                    'translation_key' => $item['key']
                ], 'key_id');
            } else {
                $keyId = $record->key_id;
            }

            // Language 1 (EN)
            $valEn = DB::table('translation_values')->where('key_id', $keyId)->where('language_id', 1)->first();
            if (!$valEn) {
                DB::table('translation_values')->insert([
                    'key_id'      => $keyId,
                    'language_id' => 1,
                    'translation' => $item['en']
                ]);
            }

            // Language 2 (PB)
            $valPb = DB::table('translation_values')->where('key_id', $keyId)->where('language_id', 2)->first();
            if (!$valPb) {
                DB::table('translation_values')->insert([
                    'key_id'      => $keyId,
                    'language_id' => 2,
                    'translation' => $item['pb']
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'Header.HeadLine',
            'NOTICEBOARD.TITLE',
            'NOTICEBOARD.SERIAL_NO',
            'NOTICEBOARD.DESCRIPTION',
            'NOTICEBOARD.PUBLISH_DATE',
            'NOTICEBOARD.DOWNLOAD',
            'NOTICEBOARD.NO_RECORDS',
            'TICKER.ATTENTION',
            'TICKER.DEFAULT_MESSAGE',
            'WORK_GALLERY.TITLE',
            'WORK_GALLERY.VIEW_ALL',
            'WORK_GALLERY.IMAGE_COMING_SOON',
            'WORK_GALLERY.SHOWING',
            'WORK_GALLERY.OF',
            'WORK_GALLERY.NO_ITEMS',
            'FOOTER.POLICIES_AND_HELP',
            'FOOTER.SITE_STATISTICS',
            'FOOTER.TOTAL_VISITORS',
            'FOOTER.LAST_UPDATED',
            'FOOTER.ALL_RIGHTS_RESERVED',
            'FOOTER.PRIVACY_POLICY',
            'FOOTER.TERMS_OF_USE',
            'FOOTER.ACCESSIBILITY',
            'FOOTER.SITEMAP',
            'FOOTER.FOLLOWS'
        ];

        $keyIds = DB::table('translations')->whereIn('translation_key', $keys)->pluck('key_id')->toArray();
        if (!empty($keyIds)) {
            DB::table('translation_values')->whereIn('key_id', $keyIds)->delete();
            DB::table('translations')->whereIn('key_id', $keyIds)->delete();
        }
    }
};
