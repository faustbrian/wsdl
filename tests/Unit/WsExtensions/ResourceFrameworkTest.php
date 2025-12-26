<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\ResourceFramework\GetResourceProperty;
use Cline\WsdlBuilder\WsExtensions\ResourceFramework\Resource;
use Cline\WsdlBuilder\WsExtensions\ResourceFramework\ResourceFrameworkPolicy;
use Cline\WsdlBuilder\WsExtensions\ResourceFramework\ResourceLifetime;
use Cline\WsdlBuilder\WsExtensions\ResourceFramework\ResourceProperties;
use Cline\WsdlBuilder\WsExtensions\ResourceFramework\ResourceProperty;
use Cline\WsdlBuilder\WsExtensions\ResourceFramework\SetResourceProperties;

describe('ResourceFramework', function (): void {
    test('Resource with properties and lifetime', function (): void {
        $endpointReference = new EndpointReference('http://example.com/resource');
        $resource = new Resource($endpointReference);

        $resource->resourceProperties()
            ->addProperty('status', 'xsd:string')
            ->modifiable()
            ->subscribable();

        $terminationTime = new DateTimeImmutable('+1 hour');
        $resource->resourceLifetime()
            ->terminationTime($terminationTime)
            ->scheduledTermination();

        expect($resource->getEndpointReference())->toBe($endpointReference);
        expect($resource->getResourceProperties())->toBeInstanceOf(ResourceProperties::class);
        expect($resource->getResourceLifetime())->toBeInstanceOf(ResourceLifetime::class);

        $config = $resource->getConfig();
        expect($config)->toHaveKey('endpointReference');
        expect($config)->toHaveKey('resourceProperties');
        expect($config)->toHaveKey('resourceLifetime');
        expect($config['endpointReference']['address'])->toBe('http://example.com/resource');
        expect($config['resourceProperties']['properties'])->toHaveCount(1);
        expect($config['resourceLifetime']['terminationTime'])->toBe($terminationTime->format(DateTimeInterface::ATOM));
    });

    test('ResourceProperties document', function (): void {
        $properties = new ResourceProperties();

        $properties->addProperty('name', 'xsd:string');
        $properties->addProperty('count', 'xsd:int')
            ->modifiable();
        $properties->addQueryExpressionDialect('http://www.w3.org/TR/1999/REC-xpath-19991116');

        expect($properties->getProperties())->toHaveCount(2);
        expect($properties->getQueryExpressionDialects())->toHaveCount(1);
        expect($properties->getQueryExpressionDialects()[0])->toBe('http://www.w3.org/TR/1999/REC-xpath-19991116');

        $config = $properties->getConfig();
        expect($config['properties'])->toHaveCount(2);
        expect($config['properties'][0]['name'])->toBe('name');
        expect($config['properties'][0]['type'])->toBe('xsd:string');
        expect($config['properties'][1]['name'])->toBe('count');
        expect($config['properties'][1]['modifiable'])->toBeTrue();
        expect($config['queryExpressionDialects'])->toHaveCount(1);
    });

    test('ResourceProperty modifiable and subscribable', function (): void {
        $property = new ResourceProperty('status', 'xsd:string');

        expect($property->getName())->toBe('status');
        expect($property->getType())->toBe('xsd:string');
        expect($property->isModifiable())->toBeFalse();
        expect($property->isSubscribable())->toBeFalse();

        $property->modifiable()->subscribable();

        expect($property->isModifiable())->toBeTrue();
        expect($property->isSubscribable())->toBeTrue();

        $config = $property->getConfig();
        expect($config['name'])->toBe('status');
        expect($config['type'])->toBe('xsd:string');
        expect($config['modifiable'])->toBeTrue();
        expect($config['subscribable'])->toBeTrue();
    });

    test('ResourceLifetime termination', function (): void {
        $lifetime = new ResourceLifetime();

        expect($lifetime->getCurrentTime())->toBeInstanceOf(DateTimeInterface::class);
        expect($lifetime->getTerminationTime())->toBeNull();
        expect($lifetime->isScheduledTermination())->toBeFalse();
        expect($lifetime->isImmediateTermination())->toBeFalse();

        $terminationTime = new DateTimeImmutable('+2 hours');
        $currentTime = new DateTimeImmutable();

        $lifetime->terminationTime($terminationTime)
            ->currentTime($currentTime)
            ->scheduledTermination();

        expect($lifetime->getTerminationTime())->toBe($terminationTime);
        expect($lifetime->getCurrentTime())->toBe($currentTime);
        expect($lifetime->isScheduledTermination())->toBeTrue();

        $config = $lifetime->getConfig();
        expect($config['terminationTime'])->toBe($terminationTime->format(DateTimeInterface::ATOM));
        expect($config['currentTime'])->toBe($currentTime->format(DateTimeInterface::ATOM));
        expect($config['scheduledTermination'])->toBeTrue();
        expect($config['immediateTermination'])->toBeFalse();
    });

    test('GetResourceProperty operation', function (): void {
        $getProperty = new GetResourceProperty('wsrf-rp:TerminationTime');

        expect($getProperty->getResourceProperty())->toBe('wsrf-rp:TerminationTime');

        $config = $getProperty->getConfig();
        expect($config['resourceProperty'])->toBe('wsrf-rp:TerminationTime');
    });

    test('SetResourceProperties operations', function (): void {
        $setProperties = new SetResourceProperties();

        $setProperties->insert('name', 'John Doe')
            ->update('status', 'active')
            ->delete('obsolete');

        expect($setProperties->getInsert())->toHaveCount(1);
        expect($setProperties->getUpdate())->toHaveCount(1);
        expect($setProperties->getDelete())->toHaveCount(1);

        expect($setProperties->getInsert()[0]['name'])->toBe('name');
        expect($setProperties->getInsert()[0]['value'])->toBe('John Doe');
        expect($setProperties->getUpdate()[0]['name'])->toBe('status');
        expect($setProperties->getUpdate()[0]['value'])->toBe('active');
        expect($setProperties->getDelete()[0])->toBe('obsolete');

        $config = $setProperties->getConfig();
        expect($config['insert'])->toHaveCount(1);
        expect($config['update'])->toHaveCount(1);
        expect($config['delete'])->toHaveCount(1);
    });

    test('ResourceFrameworkPolicy factory methods', function (): void {
        $resource = ResourceFrameworkPolicy::resource('http://example.com/resource');
        expect($resource)->toBeInstanceOf(Resource::class);
        expect($resource->getEndpointReference()->getAddress())->toBe('http://example.com/resource');

        $properties = ResourceFrameworkPolicy::resourceProperties();
        expect($properties)->toBeInstanceOf(ResourceProperties::class);

        $lifetime = ResourceFrameworkPolicy::lifetime();
        expect($lifetime)->toBeInstanceOf(ResourceLifetime::class);

        $getProperty = ResourceFrameworkPolicy::getResourceProperty('wsrf-rp:TerminationTime');
        expect($getProperty)->toBeInstanceOf(GetResourceProperty::class);
        expect($getProperty->getResourceProperty())->toBe('wsrf-rp:TerminationTime');

        $setProperties = ResourceFrameworkPolicy::setResourceProperties();
        expect($setProperties)->toBeInstanceOf(SetResourceProperties::class);
    });

    test('Resource integration', function (): void {
        $resource = ResourceFrameworkPolicy::resource('http://example.com/stateful-service');

        $resource->resourceProperties()
            ->addProperty('serviceStatus', 'xsd:string')
            ->modifiable()
            ->subscribable()
            ->end()
            ->addProperty('requestCount', 'xsd:int')
            ->modifiable()
            ->end()
            ->addQueryExpressionDialect('http://www.w3.org/TR/1999/REC-xpath-19991116');

        $terminationTime = new DateTimeImmutable('+3 hours');
        $resource->resourceLifetime()
            ->terminationTime($terminationTime)
            ->scheduledTermination();

        $config = $resource->getConfig();

        expect($config['endpointReference']['address'])->toBe('http://example.com/stateful-service');
        expect($config['resourceProperties']['properties'])->toHaveCount(2);
        expect($config['resourceProperties']['properties'][0]['name'])->toBe('serviceStatus');
        expect($config['resourceProperties']['properties'][0]['modifiable'])->toBeTrue();
        expect($config['resourceProperties']['properties'][0]['subscribable'])->toBeTrue();
        expect($config['resourceProperties']['properties'][1]['name'])->toBe('requestCount');
        expect($config['resourceProperties']['properties'][1]['modifiable'])->toBeTrue();
        expect($config['resourceProperties']['properties'][1]['subscribable'])->toBeFalse();
        expect($config['resourceProperties']['queryExpressionDialects'])->toHaveCount(1);
        expect($config['resourceLifetime']['terminationTime'])->toBe($terminationTime->format(DateTimeInterface::ATOM));
        expect($config['resourceLifetime']['scheduledTermination'])->toBeTrue();
    });

    test('SetResourceProperties multiple operations', function (): void {
        $setProperties = ResourceFrameworkPolicy::setResourceProperties();

        $setProperties->insert('newField1', 'value1')
            ->insert('newField2', 100)
            ->update('existingField', 'newValue')
            ->update('counter', 42)
            ->delete('oldField1')
            ->delete('oldField2');

        expect($setProperties->getInsert())->toHaveCount(2);
        expect($setProperties->getUpdate())->toHaveCount(2);
        expect($setProperties->getDelete())->toHaveCount(2);

        $config = $setProperties->getConfig();
        expect($config['insert'][0])->toBe(['name' => 'newField1', 'value' => 'value1']);
        expect($config['insert'][1])->toBe(['name' => 'newField2', 'value' => 100]);
        expect($config['update'][0])->toBe(['name' => 'existingField', 'value' => 'newValue']);
        expect($config['update'][1])->toBe(['name' => 'counter', 'value' => 42]);
        expect($config['delete'][0])->toBe('oldField1');
        expect($config['delete'][1])->toBe('oldField2');
    });

    test('ResourceLifetime immediate termination', function (): void {
        $lifetime = ResourceFrameworkPolicy::lifetime();

        $lifetime->immediateTermination();

        expect($lifetime->isImmediateTermination())->toBeTrue();
        expect($lifetime->isScheduledTermination())->toBeFalse();

        $config = $lifetime->getConfig();
        expect($config['immediateTermination'])->toBeTrue();
        expect($config['scheduledTermination'])->toBeFalse();
    });

    test('Resource with endpoint reference metadata', function (): void {
        $endpointReference = new EndpointReference('http://example.com/resource');
        $endpointReference->metadata();

        $resource = new Resource($endpointReference);

        $config = $resource->getConfig();
        expect($config['endpointReference'])->toHaveKey('metadata');
    });

    test('Resource fluent chaining with end()', function (): void {
        $properties = new ResourceProperties();
        $property = $properties->addProperty('name', 'xsd:string');

        $returnedProperties = $property->end();
        expect($returnedProperties)->toBe($properties);

        $config = $property->end()->getConfig();
        expect($config['properties'])->toHaveCount(1);
    });

    test('ResourceFrameworkPolicy namespace constants', function (): void {
        expect(ResourceFrameworkPolicy::NAMESPACE_WSRF_R)->toBe('http://docs.oasis-open.org/wsrf/r-2');
        expect(ResourceFrameworkPolicy::NAMESPACE_WSRF_RP)->toBe('http://docs.oasis-open.org/wsrf/rp-2');
        expect(ResourceFrameworkPolicy::NAMESPACE_WSRF_RL)->toBe('http://docs.oasis-open.org/wsrf/rl-2');
    });
});
