<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Discovery\Bye;
use Cline\WsdlBuilder\WsExtensions\Discovery\DiscoveryPolicy;
use Cline\WsdlBuilder\WsExtensions\Discovery\Enums\ScopeMatchType;
use Cline\WsdlBuilder\WsExtensions\Discovery\Hello;
use Cline\WsdlBuilder\WsExtensions\Discovery\Probe;
use Cline\WsdlBuilder\WsExtensions\Discovery\ProbeMatch;
use Cline\WsdlBuilder\WsExtensions\Discovery\Scopes;

describe('ScopeMatchType', function (): void {
    describe('Happy Paths', function (): void {
        test('has RFC3986 case', function (): void {
            expect(ScopeMatchType::RFC3986->value)
                ->toBe('http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/rfc3986');
        });

        test('has UUID case', function (): void {
            expect(ScopeMatchType::UUID->value)
                ->toBe('http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/uuid');
        });

        test('has LDAP case', function (): void {
            expect(ScopeMatchType::LDAP->value)
                ->toBe('http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/ldap');
        });

        test('has Strcmp0 case', function (): void {
            expect(ScopeMatchType::Strcmp0->value)
                ->toBe('http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/strcmp0');
        });

        test('has None case', function (): void {
            expect(ScopeMatchType::None->value)
                ->toBe('http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/none');
        });
    });
});

describe('Scopes', function (): void {
    describe('Happy Paths', function (): void {
        test('creates Scopes with RFC3986 matching', function (): void {
            $scopes = Scopes::rfc3986(['http://example.com/scope1', 'http://example.com/scope2']);

            expect($scopes->getMatchBy())->toBe(ScopeMatchType::RFC3986)
                ->and($scopes->getValues())->toBe(['http://example.com/scope1', 'http://example.com/scope2']);
        });

        test('creates Scopes with UUID matching', function (): void {
            $scopes = Scopes::uuid(['urn:uuid:123e4567-e89b-12d3-a456-426614174000']);

            expect($scopes->getMatchBy())->toBe(ScopeMatchType::UUID)
                ->and($scopes->getValues())->toBe(['urn:uuid:123e4567-e89b-12d3-a456-426614174000']);
        });

        test('creates Scopes with LDAP matching', function (): void {
            $scopes = Scopes::ldap(['ldap://example.com/ou=services']);

            expect($scopes->getMatchBy())->toBe(ScopeMatchType::LDAP)
                ->and($scopes->getValues())->toBe(['ldap://example.com/ou=services']);
        });

        test('creates Scopes with Strcmp0 matching', function (): void {
            $scopes = Scopes::strcmp0(['scope1', 'scope2']);

            expect($scopes->getMatchBy())->toBe(ScopeMatchType::Strcmp0)
                ->and($scopes->getValues())->toBe(['scope1', 'scope2']);
        });

        test('creates Scopes with None matching', function (): void {
            $scopes = Scopes::none(['http://example.com/scope']);

            expect($scopes->getMatchBy())->toBe(ScopeMatchType::None)
                ->and($scopes->getValues())->toBe(['http://example.com/scope']);
        });

        test('creates Scopes with empty values', function (): void {
            $scopes = Scopes::rfc3986([]);

            expect($scopes->getValues())->toBe([]);
        });

        test('creates Scopes using constructor', function (): void {
            $scopes = new Scopes(ScopeMatchType::UUID, ['urn:uuid:test']);

            expect($scopes->getMatchBy())->toBe(ScopeMatchType::UUID)
                ->and($scopes->getValues())->toBe(['urn:uuid:test']);
        });

        test('creates Scopes with default matching algorithm', function (): void {
            $scopes = new Scopes(values: ['http://example.com/scope']);

            expect($scopes->getMatchBy())->toBe(ScopeMatchType::RFC3986);
        });
    });
});

describe('Hello', function (): void {
    describe('Happy Paths', function (): void {
        test('creates Hello message with address', function (): void {
            $hello = Hello::create('urn:uuid:device-123');

            expect($hello->getEndpointReference())->toBeInstanceOf(EndpointReference::class)
                ->and($hello->getEndpointReference()->getAddress())->toBe('urn:uuid:device-123')
                ->and($hello->getTypes())->toBe([])
                ->and($hello->getScopes())->toBeNull()
                ->and($hello->getXAddrs())->toBe([])
                ->and($hello->getMetadataVersion())->toBe(1);
        });

        test('creates Hello message with types', function (): void {
            $hello = Hello::create(
                'urn:uuid:device-123',
                ['tns:PrinterService', 'tns:ScannerService'],
            );

            expect($hello->getTypes())->toBe(['tns:PrinterService', 'tns:ScannerService']);
        });

        test('creates Hello message with scopes', function (): void {
            $scopes = Scopes::rfc3986(['http://example.com/scope1']);
            $hello = Hello::create('urn:uuid:device-123', scopes: $scopes);

            expect($hello->getScopes())->toBe($scopes)
                ->and($hello->getScopes()->getMatchBy())->toBe(ScopeMatchType::RFC3986);
        });

        test('creates Hello message with transport addresses', function (): void {
            $hello = Hello::create(
                'urn:uuid:device-123',
                xAddrs: ['http://192.168.1.100:8080/service', 'https://device.example.com/service'],
            );

            expect($hello->getXAddrs())->toBe(['http://192.168.1.100:8080/service', 'https://device.example.com/service']);
        });

        test('creates Hello message with metadata version', function (): void {
            $hello = Hello::create('urn:uuid:device-123', metadataVersion: 5);

            expect($hello->getMetadataVersion())->toBe(5);
        });

        test('creates complete Hello message', function (): void {
            $scopes = Scopes::rfc3986(['http://example.com/printers']);
            $hello = Hello::create(
                'urn:uuid:printer-123',
                ['tns:PrinterService'],
                $scopes,
                ['http://192.168.1.100:8080/printer'],
                2,
            );

            expect($hello->getEndpointReference()->getAddress())->toBe('urn:uuid:printer-123')
                ->and($hello->getTypes())->toBe(['tns:PrinterService'])
                ->and($hello->getScopes())->toBe($scopes)
                ->and($hello->getXAddrs())->toBe(['http://192.168.1.100:8080/printer'])
                ->and($hello->getMetadataVersion())->toBe(2);
        });

        test('creates Hello using constructor', function (): void {
            $epr = new EndpointReference('urn:uuid:device-123');
            $hello = new Hello($epr, ['tns:Service']);

            expect($hello->getEndpointReference())->toBe($epr)
                ->and($hello->getTypes())->toBe(['tns:Service']);
        });
    });
});

describe('Bye', function (): void {
    describe('Happy Paths', function (): void {
        test('creates Bye message with address', function (): void {
            $bye = Bye::create('urn:uuid:device-123');

            expect($bye->getEndpointReference())->toBeInstanceOf(EndpointReference::class)
                ->and($bye->getEndpointReference()->getAddress())->toBe('urn:uuid:device-123');
        });

        test('creates Bye using constructor', function (): void {
            $epr = new EndpointReference('urn:uuid:device-456');
            $bye = new Bye($epr);

            expect($bye->getEndpointReference())->toBe($epr)
                ->and($bye->getEndpointReference()->getAddress())->toBe('urn:uuid:device-456');
        });
    });
});

describe('Probe', function (): void {
    describe('Happy Paths', function (): void {
        test('creates Probe with no filters', function (): void {
            $probe = Probe::create();

            expect($probe->getTypes())->toBe([])
                ->and($probe->getScopes())->toBeNull();
        });

        test('creates Probe with types', function (): void {
            $probe = Probe::create(['tns:PrinterService', 'tns:ScannerService']);

            expect($probe->getTypes())->toBe(['tns:PrinterService', 'tns:ScannerService'])
                ->and($probe->getScopes())->toBeNull();
        });

        test('creates Probe with scopes', function (): void {
            $scopes = Scopes::rfc3986(['http://example.com/scope']);
            $probe = Probe::create(scopes: $scopes);

            expect($probe->getTypes())->toBe([])
                ->and($probe->getScopes())->toBe($scopes);
        });

        test('creates Probe with types and scopes', function (): void {
            $scopes = Scopes::uuid(['urn:uuid:test']);
            $probe = Probe::create(['tns:Service'], $scopes);

            expect($probe->getTypes())->toBe(['tns:Service'])
                ->and($probe->getScopes())->toBe($scopes);
        });

        test('creates Probe for specific types using forTypes', function (): void {
            $probe = Probe::forTypes(['tns:PrinterService']);

            expect($probe->getTypes())->toBe(['tns:PrinterService'])
                ->and($probe->getScopes())->toBeNull();
        });

        test('creates Probe for specific scopes using inScopes', function (): void {
            $scopes = Scopes::rfc3986(['http://example.com/scope']);
            $probe = Probe::inScopes($scopes);

            expect($probe->getTypes())->toBe([])
                ->and($probe->getScopes())->toBe($scopes);
        });

        test('creates Probe using constructor', function (): void {
            $scopes = Scopes::ldap(['ldap://example.com/ou=services']);
            $probe = new Probe(['tns:Service'], $scopes);

            expect($probe->getTypes())->toBe(['tns:Service'])
                ->and($probe->getScopes())->toBe($scopes);
        });
    });
});

describe('ProbeMatch', function (): void {
    describe('Happy Paths', function (): void {
        test('creates ProbeMatch with address', function (): void {
            $match = ProbeMatch::create('urn:uuid:device-123');

            expect($match->getEndpointReference())->toBeInstanceOf(EndpointReference::class)
                ->and($match->getEndpointReference()->getAddress())->toBe('urn:uuid:device-123')
                ->and($match->getTypes())->toBe([])
                ->and($match->getScopes())->toBeNull()
                ->and($match->getXAddrs())->toBe([])
                ->and($match->getMetadataVersion())->toBe(1);
        });

        test('creates ProbeMatch with types', function (): void {
            $match = ProbeMatch::create(
                'urn:uuid:device-123',
                ['tns:PrinterService', 'tns:ScannerService'],
            );

            expect($match->getTypes())->toBe(['tns:PrinterService', 'tns:ScannerService']);
        });

        test('creates ProbeMatch with scopes', function (): void {
            $scopes = Scopes::rfc3986(['http://example.com/scope1']);
            $match = ProbeMatch::create('urn:uuid:device-123', scopes: $scopes);

            expect($match->getScopes())->toBe($scopes);
        });

        test('creates ProbeMatch with transport addresses', function (): void {
            $match = ProbeMatch::create(
                'urn:uuid:device-123',
                xAddrs: ['http://192.168.1.100:8080/service'],
            );

            expect($match->getXAddrs())->toBe(['http://192.168.1.100:8080/service']);
        });

        test('creates ProbeMatch with metadata version', function (): void {
            $match = ProbeMatch::create('urn:uuid:device-123', metadataVersion: 3);

            expect($match->getMetadataVersion())->toBe(3);
        });

        test('creates complete ProbeMatch', function (): void {
            $scopes = Scopes::uuid(['urn:uuid:scope-123']);
            $match = ProbeMatch::create(
                'urn:uuid:printer-123',
                ['tns:PrinterService'],
                $scopes,
                ['http://192.168.1.100:8080/printer'],
                4,
            );

            expect($match->getEndpointReference()->getAddress())->toBe('urn:uuid:printer-123')
                ->and($match->getTypes())->toBe(['tns:PrinterService'])
                ->and($match->getScopes())->toBe($scopes)
                ->and($match->getXAddrs())->toBe(['http://192.168.1.100:8080/printer'])
                ->and($match->getMetadataVersion())->toBe(4);
        });

        test('creates ProbeMatch using constructor', function (): void {
            $epr = new EndpointReference('urn:uuid:device-123');
            $scopes = Scopes::strcmp0(['scope1']);
            $match = new ProbeMatch($epr, ['tns:Service'], $scopes);

            expect($match->getEndpointReference())->toBe($epr)
                ->and($match->getTypes())->toBe(['tns:Service'])
                ->and($match->getScopes())->toBe($scopes);
        });
    });
});

describe('DiscoveryPolicy', function (): void {
    describe('Happy Paths', function (): void {
        test('creates discoverable policy', function (): void {
            $policy = DiscoveryPolicy::discoverable();

            expect($policy)->toBe([
                'type' => 'wsd:Discoverable',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'enabled' => true,
            ]);
        });

        test('creates adhoc mode policy', function (): void {
            $policy = DiscoveryPolicy::adhoc();

            expect($policy)->toBe([
                'type' => 'wsd:DiscoveryMode',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'mode' => 'adhoc',
            ]);
        });

        test('creates managed mode policy without proxy address', function (): void {
            $policy = DiscoveryPolicy::managed();

            expect($policy)->toBe([
                'type' => 'wsd:DiscoveryMode',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'mode' => 'managed',
            ]);
        });

        test('creates managed mode policy with proxy address', function (): void {
            $policy = DiscoveryPolicy::managed('http://proxy.example.com:8080/discovery');

            expect($policy)->toBe([
                'type' => 'wsd:DiscoveryMode',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'mode' => 'managed',
                'proxyAddress' => 'http://proxy.example.com:8080/discovery',
            ]);
        });

        test('creates discovery endpoint policy', function (): void {
            $policy = DiscoveryPolicy::discoveryEndpoint('http://discovery.example.com/endpoint');

            expect($policy)->toBe([
                'type' => 'wsd:DiscoveryEndpoint',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'address' => 'http://discovery.example.com/endpoint',
            ]);
        });

        test('creates suppression policy with no suppression', function (): void {
            $policy = DiscoveryPolicy::suppression();

            expect($policy)->toBe([
                'type' => 'wsd:Suppression',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'suppressHello' => false,
                'suppressBye' => false,
            ]);
        });

        test('creates suppression policy with Hello suppressed', function (): void {
            $policy = DiscoveryPolicy::suppression(suppressHello: true);

            expect($policy)->toBe([
                'type' => 'wsd:Suppression',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'suppressHello' => true,
                'suppressBye' => false,
            ]);
        });

        test('creates suppression policy with Bye suppressed', function (): void {
            $policy = DiscoveryPolicy::suppression(suppressBye: true);

            expect($policy)->toBe([
                'type' => 'wsd:Suppression',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'suppressHello' => false,
                'suppressBye' => true,
            ]);
        });

        test('creates suppression policy with both suppressed', function (): void {
            $policy = DiscoveryPolicy::suppression(true, true);

            expect($policy)->toBe([
                'type' => 'wsd:Suppression',
                'namespace' => 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01',
                'suppressHello' => true,
                'suppressBye' => true,
            ]);
        });

        test('has correct namespace URI constant', function (): void {
            expect(DiscoveryPolicy::NAMESPACE_URI)
                ->toBe('http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01');
        });
    });
});

describe('Discovery Integration', function (): void {
    describe('Happy Paths', function (): void {
        test('complete discovery workflow with Hello and Bye', function (): void {
            // Service announces itself
            $scopes = Scopes::rfc3986(['http://example.com/printers']);
            $hello = Hello::create(
                'urn:uuid:printer-123',
                ['tns:PrinterService'],
                $scopes,
                ['http://192.168.1.100:8080/printer'],
            );

            expect($hello->getEndpointReference()->getAddress())->toBe('urn:uuid:printer-123')
                ->and($hello->getTypes())->toContain('tns:PrinterService');

            // Service goes offline
            $bye = Bye::create('urn:uuid:printer-123');

            expect($bye->getEndpointReference()->getAddress())->toBe('urn:uuid:printer-123');
        });

        test('complete discovery workflow with Probe and ProbeMatch', function (): void {
            // Client searches for printers
            $searchScopes = Scopes::rfc3986(['http://example.com/printers']);
            $probe = Probe::create(['tns:PrinterService'], $searchScopes);

            expect($probe->getTypes())->toContain('tns:PrinterService')
                ->and($probe->getScopes())->toBe($searchScopes);

            // Service responds with match
            $match = ProbeMatch::create(
                'urn:uuid:printer-123',
                ['tns:PrinterService'],
                $searchScopes,
                ['http://192.168.1.100:8080/printer'],
            );

            expect($match->getEndpointReference()->getAddress())->toBe('urn:uuid:printer-123')
                ->and($match->getTypes())->toBe($probe->getTypes())
                ->and($match->getXAddrs())->toContain('http://192.168.1.100:8080/printer');
        });

        test('multiple services with different scope matching', function (): void {
            // Service 1 with RFC3986 scopes
            $scopes1 = Scopes::rfc3986(['http://example.com/department/it']);
            $hello1 = Hello::create('urn:uuid:service-1', ['tns:Service'], $scopes1);

            // Service 2 with UUID scopes
            $scopes2 = Scopes::uuid(['urn:uuid:dept-123']);
            $hello2 = Hello::create('urn:uuid:service-2', ['tns:Service'], $scopes2);

            // Service 3 with LDAP scopes
            $scopes3 = Scopes::ldap(['ldap://example.com/ou=services,dc=example,dc=com']);
            $hello3 = Hello::create('urn:uuid:service-3', ['tns:Service'], $scopes3);

            expect($hello1->getScopes()->getMatchBy())->toBe(ScopeMatchType::RFC3986)
                ->and($hello2->getScopes()->getMatchBy())->toBe(ScopeMatchType::UUID)
                ->and($hello3->getScopes()->getMatchBy())->toBe(ScopeMatchType::LDAP);
        });

        test('probe with multiple types and response matching', function (): void {
            // Search for multiple service types
            $probe = Probe::forTypes(['tns:PrinterService', 'tns:ScannerService', 'tns:FaxService']);

            // Multi-function device responds
            $match = ProbeMatch::create(
                'urn:uuid:mfd-123',
                ['tns:PrinterService', 'tns:ScannerService', 'tns:FaxService'],
                xAddrs: ['http://192.168.1.100:8080/mfd'],
            );

            expect($match->getTypes())->toBe($probe->getTypes())
                ->and($match->getTypes())->toHaveCount(3);
        });

        test('discovery policies for different deployment modes', function (): void {
            // Adhoc mode for small networks
            $adhocPolicy = DiscoveryPolicy::adhoc();
            expect($adhocPolicy['mode'])->toBe('adhoc');

            // Managed mode for enterprise
            $managedPolicy = DiscoveryPolicy::managed('http://proxy.corp.example.com/discovery');
            expect($managedPolicy['mode'])->toBe('managed')
                ->and($managedPolicy['proxyAddress'])->toBe('http://proxy.corp.example.com/discovery');

            // Discoverable policy
            $discoverablePolicy = DiscoveryPolicy::discoverable();
            expect($discoverablePolicy['enabled'])->toBeTrue();
        });

        test('metadata version tracking across messages', function (): void {
            // Initial announcement
            $hello = Hello::create('urn:uuid:device-123', metadataVersion: 1);
            expect($hello->getMetadataVersion())->toBe(1);

            // Service updated, new metadata version
            $hello2 = Hello::create('urn:uuid:device-123', metadataVersion: 2);
            expect($hello2->getMetadataVersion())->toBe(2);

            // ProbeMatch with latest version
            $match = ProbeMatch::create('urn:uuid:device-123', metadataVersion: 2);
            expect($match->getMetadataVersion())->toBe(2);
        });
    });
});
