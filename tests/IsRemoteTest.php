<?php

namespace Tests;

use JesseGall\Resources\Api;
use JesseGall\Resources\Concerns\IsRemote;
use JesseGall\Resources\Exceptions\ApiException;
use JesseGall\Resources\RemoteResource;
use JesseGall\Resources\Resource;
use PHPUnit\Framework\TestCase;

class IsRemoteTest extends TestCase
{

    protected function tearDown(): void
    {
        parent::tearDown();

        TestRemoteResource::setApi(new TestApi());
    }

    # --- isRemote::count() ---

    public function test__When_count__Then_correct_count()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function count(string $resource, array $params = []): int
            {
                return 5;
            }
        });

        $this->assertEquals(5, TestRemoteResource::count());
    }

    public function test__Given_ApiException__When_count__Then_zero()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function count(string $resource, array $params = []): int
            {
                throw new ApiException($resource);
            }
        });

        $this->assertEquals(0, TestRemoteResource::count());
    }

    # --- isRemote::all() ---

    public function test__When_all__Then_collection_has_correct_count()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                return [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ];
            }
        });

        $this->assertCount(3, TestRemoteResource::all());
    }

    public function test__When_all__Then_collection_has_correct_type()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                return [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ];
            }
        });

        $collection = TestRemoteResource::all();

        $this->assertEquals(TestRemoteResource::class, $collection->getType());
    }

    public function test__Given_api_returns_data_not_as_list__When_all__Then_data_is_wrapped_and_count_is_one()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                return ['id' => 1];
            }
        });

        $collection = TestRemoteResource::all();

        $this->assertCount(1, $collection);
    }

    public function test__Given_api_throws_exception__When_all__Then_collection_is_empty()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                throw new ApiException($resource);
            }
        });

        $collection = TestRemoteResource::all();

        $this->assertCount(0, $collection);
    }

    public function test__When_all__Then_resources_exist()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                return [
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ];
            }
        });

        $collection = TestRemoteResource::all();

        $this->assertCount(3, $collection->filter(fn($resource) => $resource->exists()));
    }

    # --- isRemote::find() ---

    public function test__When_find__Then_resource_contains_data_from_api()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                return ['id' => 1, 'name' => 'Test'];
            }
        });

        $resource = TestRemoteResource::find(1);

        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Test', $resource->get('name'));
    }

    public function test__Given_api_throws_exception__When_find__Then_null()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                throw new ApiException($resource);
            }
        });

        $resource = TestRemoteResource::find(1);

        $this->assertNull($resource);
    }

    public function test__When_find__Then_resource_exists()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, $id = null, array $params = []): array
            {
                return ['id' => 1, 'name' => 'Test'];
            }
        });

        $resource = TestRemoteResource::find(1);

        $this->assertTrue($resource->exists());
    }

    # --- isRemote::create() ---

    public function test__When_create__Then_resource_data_is_data_from_api()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function create(string $resource, array $data = []): array
            {
                return ['id' => 1, ...$data];
            }
        });

        $resource = TestRemoteResource::create(['name' => 'Test']);

        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Test', $resource->get('name'));
    }

    public function test__Given_api_throws_exception__When_create__Then_null()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function create(string $resource, array $data = []): array
            {
                throw new ApiException($resource);
            }
        });

        $resource = TestRemoteResource::create();

        $this->assertNull($resource);
    }

    public function test__When_create__Then_resource_exists()
    {
        $resource = TestRemoteResource::create();

        $this->assertTrue($resource->exists());
    }

    # --- isRemote::save() ---

    public function test__Given_resource_exists__When_save__Then_correct_arguments_are_passed_to_api()
    {
        $api = $this->createMock(TestApi::class);

        $api->expects($this->once())
            ->method('update')
            ->with(TestRemoteResource::class, 1, ['id' => 1, 'name' => 'Test']);

        TestRemoteResource::setApi($api);

        $resource = new TestRemoteResource(['id' => 1, 'name' => 'Test']);

        $resource->setExists(true);

        $resource->save();
    }

    public function test__Given_resource_does_not_exist__When_save__Then_correct_arguments_are_passed_to_api()
    {
        $api = $this->createMock(TestApi::class);

        $api->expects($this->once())
            ->method('create')
            ->with(TestRemoteResource::class, ['name' => 'Test']);

        TestRemoteResource::setApi($api);

        $resource = new TestRemoteResource(['name' => 'Test']);

        $resource->setExists(false);

        $resource->save();
    }

    public function test__When_save__Then_data_from_resource_merged_with_data_from_api()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function update(string $resource, $id, array $data = []): array
            {
                return ['updated_at' => '2023-01-01'];
            }
        });

        $resource = new TestRemoteResource(['id' => 1, 'name' => 'Test', 'updated_at' => '2012-01-01']);

        $resource->setExists(true);

        $resource->save();

        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Test', $resource->get('name'));
        $this->assertEquals('2023-01-01', $resource->get('updated_at'));
    }

    public function test__Given_resource_does_not_exist__When_save__Then_resource_created()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function create(string $resource, array $data = []): array
            {
                return ['id' => 1, ...$data];
            }
        });

        $resource = new TestRemoteResource(['name' => 'Test']);

        $resource->save();

        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Test', $resource->get('name'));
    }

    public function test__Given_api_throws_exception__When_save__Then_resource_data_is_unchanged()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function update(string $resource, $id, array $data = []): array
            {
                throw new ApiException($resource);
            }
        });

        $expected = ['id' => 1, 'name' => 'Test'];

        $resource = new TestRemoteResource($expected);

        $resource->setExists(true);

        $resource->save();

        $this->assertEquals($expected, $resource->toArray());
    }

    public function test__When_save__Then_true()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $this->assertTrue($resource->save());
    }

    public function test__Given_api_throws_exception__When_save__Then_false()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function update(string $resource, $id, array $data = []): array
            {
                throw new ApiException($resource);
            }
        });

        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $this->assertFalse($resource->save());
    }

    # --- isRemote::hydrate() ---

    public function test__When_hydrate__Then_correct_arguments_are_passed_to_api()
    {
        $api = $this->createMock(Api::class);

        $api->expects($this->once())
            ->method('get')
            ->with(TestRemoteResource::class, 1)
            ->willReturn([]);

        TestRemoteResource::setApi($api);

        $resource = new TestRemoteResource(['id' => 1]);

        $resource->hydrate();
    }

    public function test__When_hydrate__Then_resource_data_is_merged_with_data_from_api()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, int|string $id = null, array $params = []): array
            {
                return ['id' => 1, 'name' => 'Test'];
            }
        });

        $resource = new TestRemoteResource([
            'id' => 1,
            'name' => 'Test',
            'local_property' => 'value'
        ]);

        $resource->hydrate();

        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Test', $resource->get('name'));
        $this->assertEquals('value', $resource->get('local_property'));
    }

    public function test__When_hydrate__Then_resource_exists()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $resource->hydrate();

        $this->assertTrue($resource->exists());
    }

    public function test__When_hydrate__Then_true()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $this->assertTrue($resource->hydrate());
    }

    public function test__Given_id_null__When_hydrate__Then_false()
    {
        $resource = new TestRemoteResource(['id' => null]);

        $this->assertFalse($resource->hydrate());
    }

    public function test__Given_api_throws_exception__When_hydrate__Then_false()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, int|string $id = null, array $params = []): array
            {
                throw new ApiException($resource);
            }
        });

        $resource = new TestRemoteResource(['id' => 1]);

        $this->assertFalse($resource->hydrate());
    }

    # --- isRemote::refresh() ---

    public function test__When_refresh__Then_correct_arguments_are_passed_to_api()
    {
        $api = $this->createMock(TestApi::class);

        $api->expects($this->once())
            ->method('get')
            ->with(TestRemoteResource::class, 1);

        TestRemoteResource::setApi($api);

        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $resource->refresh();
    }

    public function test__When_refresh__Then_all_resource_data_but_id_is_overwritten_with_data_from_api()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, int|string $id = null, array $params = []): array
            {
                return ['name' => 'Test'];
            }
        });

        $resource = new TestRemoteResource([
            'id' => 1,
            'name' => 'Test',
            'local_property' => 'value'
        ]);

        $resource->setExists(true);

        $resource->refresh();

        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Test', $resource->get('name'));
        $this->assertNull($resource->get('local_property'));
    }

    public function test__When_refresh__Then_resource_exists()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $resource->refresh();

        $this->assertTrue($resource->exists());
    }

    public function test__When_refresh__Then_true()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $this->assertTrue($resource->refresh());
    }

    public function test__Given_resource_does_not_exist__When_refresh__Then_false()
    {
        $resource = new TestRemoteResource(['id' => null]);

        $resource->setExists(false);

        $this->assertFalse($resource->refresh());
    }

    public function test__Given_api_throws_exception__When_refresh__Then_false()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function get(string $resource, int|string $id = null, array $params = []): array
            {
                throw new ApiException($resource);
            }
        });

        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $this->assertFalse($resource->refresh());
    }

    # --- isRemote::delete() ---

    public function test__When_delete__Then_correct_arguments_are_passed_to_api()
    {
        $api = $this->createMock(Api::class);

        $api->expects($this->once())
            ->method('delete')
            ->with(TestRemoteResource::class, 1);

        TestRemoteResource::setApi($api);

        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $resource->delete();
    }

    public function test__When_delete__Then_true()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $this->assertTrue($resource->delete());
    }

    public function test__Given_resource_does_not_exist__When_delete__Then_false()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(false);

        $this->assertFalse($resource->delete());
    }

    public function test__When_delete__Then_resource_does_not_exist()
    {
        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $resource->delete();

        $this->assertFalse($resource->exists());
    }

    public function test__Given_api_throws_exception__When_delete__Then_false()
    {
        TestRemoteResource::setApi(new class extends TestApi {
            public function delete(string $resource, int|string $id = null, array $params = []): void
            {
                throw new ApiException($resource);
            }
        });

        $resource = new TestRemoteResource(['id' => 1]);

        $resource->setExists(true);

        $this->assertFalse($resource->delete());
    }

}

class TestRemoteResource extends Resource implements RemoteResource
{
    use IsRemote;

    private static Api $api;

    protected static function api(): Api
    {
        return self::$api ?? new TestApi();
    }

    public static function setApi(Api $api)
    {
        self::$api = $api;
    }
}

class TestApi implements Api
{

    public function count(string $resource, array $params = []): int
    {
        return 0;
    }

    public function get(string $resource, int|string $id = null, array $params = []): array
    {
        return [];
    }

    public function create(string $resource, array $data): array
    {
        return [];
    }

    public function update(string $resource, int|string $id, array $data = []): array
    {
        return [];
    }

    public function delete(string $resource, int|string $id): void
    {
        //
    }

}

