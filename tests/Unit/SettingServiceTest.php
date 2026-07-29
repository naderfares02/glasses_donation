<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettingService $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = new SettingService();

        // SettingService يخزن كل الإعدادات تحت مفتاح كاش ثابت واحد (app.settings.all).
        // إذا كان الكاش بالتيست بيستخدم driver دائم (زي 'file' أو 'database')
        // بدل 'array'، ممكن تتسرب قيمة من تيست سابق. نمسحها يدوياً لضمان عزل التيستات.
        Cache::forget('app.settings.all');
    }

    #[Test]
    public function get_returns_the_default_when_the_key_does_not_exist(): void
    {
        $this->assertNull($this->settings->get('does.not.exist'));
        $this->assertSame('fallback', $this->settings->get('does.not.exist', 'fallback'));
    }

    #[Test]
    public function it_stores_and_retrieves_a_string_value(): void
    {
        $this->settings->set('site.name', 'Glasses Donation Platform', 'string');

        $this->assertSame('Glasses Donation Platform', $this->settings->get('site.name'));
        $this->assertDatabaseHas('settings', ['key' => 'site.name', 'type' => 'string']);
    }

    #[Test]
    public function it_stores_and_retrieves_a_text_value(): void
    {
        $this->settings->set('site.about', 'نص طويل عن المنصة', 'text');

        $this->assertSame('نص طويل عن المنصة', $this->settings->get('site.about'));
    }

    #[Test]
    public function it_casts_a_bool_value_correctly_in_both_directions(): void
    {
        $this->settings->set('auth.allow_registration', true, 'bool');
        $this->assertTrue($this->settings->get('auth.allow_registration'));

        $this->settings->set('auth.allow_registration', false, 'bool');
        $this->assertFalse($this->settings->get('auth.allow_registration'));

        // Loosely-truthy inputs must also cast correctly
        $this->settings->set('auth.require_phone_verification', '1', 'bool');
        $this->assertTrue($this->settings->get('auth.require_phone_verification'));

        $this->settings->set('auth.require_phone_verification', '0', 'bool');
        $this->assertFalse($this->settings->get('auth.require_phone_verification'));
    }

    #[Test]
    public function it_casts_an_int_value_correctly(): void
    {
        $this->settings->set('donations.max_per_month', '15', 'int');

        $value = $this->settings->get('donations.max_per_month');

        $this->assertSame(15, $value);
        $this->assertIsInt($value);
    }

    #[Test]
    public function it_stores_and_retrieves_a_json_value_as_an_array(): void
    {
        $this->settings->set('notifications.channels', ['mail', 'database'], 'json');

        $value = $this->settings->get('notifications.channels');

        $this->assertIsArray($value);
        $this->assertSame(['mail', 'database'], $value);
    }

    #[Test]
    public function a_non_array_value_saved_as_json_is_wrapped_in_an_array(): void
    {
        // castIn casts non-array values to (array) when type is 'json'
        $this->settings->set('notifications.single_channel', 'mail', 'json');

        $value = $this->settings->get('notifications.single_channel');

        $this->assertIsArray($value);
    }

    #[Test]
    public function set_many_stores_multiple_settings_at_once(): void
    {
        $this->settings->setMany([
            ['key' => 'site.name', 'value' => 'My Site', 'type' => 'string'],
            ['key' => 'site.maintenance', 'value' => true, 'type' => 'bool'],
            ['key' => 'donations.limit', 'value' => 5, 'type' => 'int'],
        ]);

        $this->assertSame('My Site', $this->settings->get('site.name'));
        $this->assertTrue($this->settings->get('site.maintenance'));
        $this->assertSame(5, $this->settings->get('donations.limit'));
    }

    #[Test]
    public function set_many_defaults_to_string_type_when_not_specified(): void
    {
        $this->settings->setMany([
            ['key' => 'site.tagline', 'value' => 'Give sight, give hope'],
        ]);

        $this->assertDatabaseHas('settings', ['key' => 'site.tagline', 'type' => 'string']);
    }

    #[Test]
    public function all_returns_every_setting_keyed_by_its_key_with_the_correct_cast(): void
    {
        $this->settings->set('site.name', 'My Site', 'string');
        $this->settings->set('site.maintenance', true, 'bool');

        $all = $this->settings->all();

        $this->assertSame('My Site', $all['site.name']);
        $this->assertTrue($all['site.maintenance']);
    }

    #[Test]
    public function results_are_cached_and_updating_the_database_directly_does_not_change_get_until_cache_is_cleared(): void
    {
        $this->settings->set('site.name', 'Original Name', 'string');
        $this->assertSame('Original Name', $this->settings->get('site.name'));

        // نغيّر القيمة مباشرة بقاعدة البيانات، متجاوزين الـ service والكاش
        Setting::where('key', 'site.name')->update(['value' => ['v' => 'Changed Directly']]);

        // القيمة القديمة لسا موجودة بالكاش
        $this->assertSame('Original Name', $this->settings->get('site.name'));

        $this->settings->clearCache();

        // بعد مسح الكاش، لازم تنقرا القيمة الجديدة من قاعدة البيانات
        $this->assertSame('Changed Directly', $this->settings->get('site.name'));
    }

    #[Test]
    public function calling_set_automatically_clears_the_cache(): void
    {
        $this->settings->set('site.name', 'First Value', 'string');
        $this->assertSame('First Value', $this->settings->get('site.name'));

        // set() لازم يمسح الكاش تلقائياً بدون داعي نستدعي clearCache()
        $this->settings->set('site.name', 'Second Value', 'string');
        $this->assertSame('Second Value', $this->settings->get('site.name'));
    }

    #[Test]
    public function setting_an_existing_key_updates_it_instead_of_creating_a_duplicate(): void
    {
        $this->settings->set('site.name', 'First Value', 'string');
        $this->settings->set('site.name', 'Second Value', 'string');

        $this->assertDatabaseCount('settings', 1);
        $this->assertDatabaseHas('settings', ['key' => 'site.name']);
    }
}
