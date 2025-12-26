<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\MetadataExchange\Enums\MetadataDialect;
use Cline\WsdlBuilder\WsExtensions\MetadataExchange\GetMetadata;
use Cline\WsdlBuilder\WsExtensions\MetadataExchange\Metadata;
use Cline\WsdlBuilder\WsExtensions\MetadataExchange\MetadataExchangePolicy;
use Cline\WsdlBuilder\WsExtensions\MetadataExchange\MetadataReference;
use Cline\WsdlBuilder\WsExtensions\MetadataExchange\MetadataSection;

describe('WS-MetadataExchange', function (): void {
    describe('MetadataDialect Enum', function (): void {
        describe('Happy Paths', function (): void {
            test('provides WSDL dialect URI', function (): void {
                // Arrange & Act
                $dialect = MetadataDialect::WSDL;

                // Assert
                expect($dialect->value)->toBe('http://schemas.xmlsoap.org/wsdl/');
            });

            test('provides XmlSchema dialect URI', function (): void {
                // Arrange & Act
                $dialect = MetadataDialect::XmlSchema;

                // Assert
                expect($dialect->value)->toBe('http://www.w3.org/2001/XMLSchema');
            });

            test('provides Policy dialect URI', function (): void {
                // Arrange & Act
                $dialect = MetadataDialect::Policy;

                // Assert
                expect($dialect->value)->toBe('http://schemas.xmlsoap.org/ws/2004/09/policy');
            });

            test('provides MEX dialect URI', function (): void {
                // Arrange & Act
                $dialect = MetadataDialect::MEX;

                // Assert
                expect($dialect->value)->toBe('http://schemas.xmlsoap.org/ws/2004/09/mex');
            });

            test('enum contains all four dialect types', function (): void {
                // Arrange & Act
                $dialects = MetadataDialect::cases();

                // Assert
                expect($dialects)->toHaveCount(4)
                    ->and($dialects)->toContain(MetadataDialect::WSDL)
                    ->and($dialects)->toContain(MetadataDialect::XmlSchema)
                    ->and($dialects)->toContain(MetadataDialect::Policy)
                    ->and($dialects)->toContain(MetadataDialect::MEX);
            });
        });
    });

    describe('GetMetadata', function (): void {
        describe('Happy Paths', function (): void {
            test('creates request with WSDL dialect', function (): void {
                // Arrange & Act
                $request = new GetMetadata(MetadataDialect::WSDL);

                // Assert
                expect($request->dialect)->toBe(MetadataDialect::WSDL)
                    ->and($request->identifier)->toBeNull();
            });

            test('creates request with XmlSchema dialect', function (): void {
                // Arrange & Act
                $request = new GetMetadata(MetadataDialect::XmlSchema);

                // Assert
                expect($request->dialect)->toBe(MetadataDialect::XmlSchema);
            });

            test('creates request with Policy dialect', function (): void {
                // Arrange & Act
                $request = new GetMetadata(MetadataDialect::Policy);

                // Assert
                expect($request->dialect)->toBe(MetadataDialect::Policy);
            });

            test('creates request with MEX dialect', function (): void {
                // Arrange & Act
                $request = new GetMetadata(MetadataDialect::MEX);

                // Assert
                expect($request->dialect)->toBe(MetadataDialect::MEX);
            });

            test('creates request with identifier', function (): void {
                // Arrange & Act
                $request = new GetMetadata(MetadataDialect::WSDL, 'urn:example:service:v1');

                // Assert
                expect($request->dialect)->toBe(MetadataDialect::WSDL)
                    ->and($request->identifier)->toBe('urn:example:service:v1');
            });

            test('converts to array without identifier', function (): void {
                // Arrange
                $request = new GetMetadata(MetadataDialect::WSDL);

                // Act
                $array = $request->toArray();

                // Assert
                expect($array)->toBe([
                    'dialect' => 'http://schemas.xmlsoap.org/wsdl/',
                ]);
            });

            test('converts to array with identifier', function (): void {
                // Arrange
                $request = new GetMetadata(MetadataDialect::Policy, 'urn:example:policy:v1');

                // Act
                $array = $request->toArray();

                // Assert
                expect($array)->toBe([
                    'dialect' => 'http://schemas.xmlsoap.org/ws/2004/09/policy',
                    'identifier' => 'urn:example:policy:v1',
                ]);
            });
        });
    });

    describe('MetadataSection', function (): void {
        describe('Happy Paths', function (): void {
            test('creates section with WSDL content', function (): void {
                // Arrange
                $wsdlContent = '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/">...</definitions>';

                // Act
                $section = new MetadataSection(
                    MetadataDialect::WSDL->value,
                    $wsdlContent,
                );

                // Assert
                expect($section->dialect)->toBe('http://schemas.xmlsoap.org/wsdl/')
                    ->and($section->content)->toBe($wsdlContent)
                    ->and($section->identifier)->toBeNull();
            });

            test('creates section with XSD content', function (): void {
                // Arrange
                $xsdContent = '<schema xmlns="http://www.w3.org/2001/XMLSchema">...</schema>';

                // Act
                $section = new MetadataSection(
                    MetadataDialect::XmlSchema->value,
                    $xsdContent,
                );

                // Assert
                expect($section->dialect)->toBe('http://www.w3.org/2001/XMLSchema')
                    ->and($section->content)->toBe($xsdContent);
            });

            test('creates section with Policy content', function (): void {
                // Arrange
                $policyContent = ['type' => 'wsp:Policy', 'assertions' => []];

                // Act
                $section = new MetadataSection(
                    MetadataDialect::Policy->value,
                    $policyContent,
                );

                // Assert
                expect($section->dialect)->toBe('http://schemas.xmlsoap.org/ws/2004/09/policy')
                    ->and($section->content)->toBe($policyContent);
            });

            test('creates section with identifier', function (): void {
                // Arrange
                $content = '<definitions>...</definitions>';

                // Act
                $section = new MetadataSection(
                    MetadataDialect::WSDL->value,
                    $content,
                    'urn:example:service:v1',
                );

                // Assert
                expect($section->identifier)->toBe('urn:example:service:v1');
            });

            test('converts to array without identifier', function (): void {
                // Arrange
                $content = '<schema>...</schema>';
                $section = new MetadataSection(
                    MetadataDialect::XmlSchema->value,
                    $content,
                );

                // Act
                $array = $section->toArray();

                // Assert
                expect($array)->toBe([
                    'dialect' => 'http://www.w3.org/2001/XMLSchema',
                    'content' => $content,
                ]);
            });

            test('converts to array with identifier', function (): void {
                // Arrange
                $content = '<definitions>...</definitions>';
                $section = new MetadataSection(
                    MetadataDialect::WSDL->value,
                    $content,
                    'urn:example:service:v1',
                );

                // Act
                $array = $section->toArray();

                // Assert
                expect($array)->toBe([
                    'dialect' => 'http://schemas.xmlsoap.org/wsdl/',
                    'content' => $content,
                    'identifier' => 'urn:example:service:v1',
                ]);
            });
        });
    });

    describe('Metadata', function (): void {
        describe('Happy Paths', function (): void {
            test('creates empty metadata container', function (): void {
                // Arrange & Act
                $metadata = new Metadata();

                // Assert
                expect($metadata->getSections())->toBeEmpty();
            });

            test('creates metadata with initial sections', function (): void {
                // Arrange
                $section1 = new MetadataSection(MetadataDialect::WSDL->value, '<definitions>...</definitions>');
                $section2 = new MetadataSection(MetadataDialect::XmlSchema->value, '<schema>...</schema>');

                // Act
                $metadata = new Metadata([$section1, $section2]);

                // Assert
                expect($metadata->getSections())->toHaveCount(2);
            });

            test('adds single metadata section', function (): void {
                // Arrange
                $metadata = new Metadata();
                $section = new MetadataSection(MetadataDialect::WSDL->value, '<definitions>...</definitions>');

                // Act
                $result = $metadata->addSection($section);

                // Assert
                expect($result)->toBe($metadata)
                    ->and($metadata->getSections())->toHaveCount(1)
                    ->and($metadata->getSections()[0])->toBe($section);
            });

            test('adds multiple metadata sections', function (): void {
                // Arrange
                $metadata = new Metadata();
                $section1 = new MetadataSection(MetadataDialect::WSDL->value, '<definitions>...</definitions>');
                $section2 = new MetadataSection(MetadataDialect::XmlSchema->value, '<schema>...</schema>');
                $section3 = new MetadataSection(MetadataDialect::Policy->value, ['policy' => 'data']);

                // Act
                $metadata->addSection($section1)
                    ->addSection($section2)
                    ->addSection($section3);

                // Assert
                expect($metadata->getSections())->toHaveCount(3);
            });

            test('converts to array with empty sections', function (): void {
                // Arrange
                $metadata = new Metadata();

                // Act
                $array = $metadata->toArray();

                // Assert
                expect($array)->toBe([
                    'metadataSections' => [],
                ]);
            });

            test('converts to array with single section', function (): void {
                // Arrange
                $metadata = new Metadata();
                $section = new MetadataSection(MetadataDialect::WSDL->value, '<definitions>...</definitions>');
                $metadata->addSection($section);

                // Act
                $array = $metadata->toArray();

                // Assert
                /** @var array{metadataSections: array<int, array<string, mixed>>} $array */
                expect($array)->toHaveKey('metadataSections')
                    ->and($array['metadataSections'])->toHaveCount(1)
                    ->and($array['metadataSections'][0])->toHaveKey('dialect')
                    ->and($array['metadataSections'][0])->toHaveKey('content');
            });

            test('converts to array with multiple sections', function (): void {
                // Arrange
                $metadata = new Metadata();
                $metadata->addSection(new MetadataSection(MetadataDialect::WSDL->value, '<definitions>...</definitions>'))
                    ->addSection(new MetadataSection(MetadataDialect::XmlSchema->value, '<schema>...</schema>'))
                    ->addSection(new MetadataSection(MetadataDialect::Policy->value, ['policy' => 'data']));

                // Act
                $array = $metadata->toArray();

                // Assert
                /** @var array{metadataSections: array<int, array<string, mixed>>} $array */
                expect($array['metadataSections'])->toHaveCount(3)
                    ->and($array['metadataSections'][0]['dialect'])->toBe('http://schemas.xmlsoap.org/wsdl/')
                    ->and($array['metadataSections'][1]['dialect'])->toBe('http://www.w3.org/2001/XMLSchema')
                    ->and($array['metadataSections'][2]['dialect'])->toBe('http://schemas.xmlsoap.org/ws/2004/09/policy');
            });
        });
    });

    describe('MetadataReference', function (): void {
        describe('Happy Paths', function (): void {
            test('creates reference with endpoint address', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');

                // Act
                $reference = new MetadataReference($address);

                // Assert
                expect($reference->getAddress())->toBe($address)
                    ->and($reference->getReferenceProperties())->toBeEmpty();
            });

            test('creates reference with reference properties', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');

                // Act
                $reference = new MetadataReference($address, ['version' => '1.0']);

                // Assert
                expect($reference->getReferenceProperties())->toBe(['version' => '1.0']);
            });

            test('adds single reference property', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');
                $reference = new MetadataReference($address);

                // Act
                $result = $reference->addReferenceProperty('version', '2.0');

                // Assert
                expect($result)->toBe($reference)
                    ->and($reference->getReferenceProperties())->toBe(['version' => '2.0']);
            });

            test('adds multiple reference properties', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');
                $reference = new MetadataReference($address);

                // Act
                $reference->addReferenceProperty('version', '1.0')
                    ->addReferenceProperty('language', 'en-US')
                    ->addReferenceProperty('format', 'xml');

                // Assert
                expect($reference->getReferenceProperties())->toHaveCount(3)
                    ->and($reference->getReferenceProperties()['version'])->toBe('1.0')
                    ->and($reference->getReferenceProperties()['language'])->toBe('en-US')
                    ->and($reference->getReferenceProperties()['format'])->toBe('xml');
            });

            test('converts to array without reference properties', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');
                $reference = new MetadataReference($address);

                // Act
                $array = $reference->toArray();

                // Assert
                expect($array)->toHaveKey('address')
                    ->and($array['address'])->toHaveKey('address', 'https://example.com/metadata')
                    ->and($array)->not()->toHaveKey('referenceProperties');
            });

            test('converts to array with reference properties', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');
                $reference = new MetadataReference($address, ['version' => '1.0']);

                // Act
                $array = $reference->toArray();

                // Assert
                expect($array)->toHaveKey('referenceProperties')
                    ->and($array['referenceProperties'])->toBe(['version' => '1.0']);
            });

            test('converts to array with endpoint reference parameters', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');
                $address->referenceParameters()->parameter('http://example.com', 'SessionId', 'abc123');
                $reference = new MetadataReference($address);

                // Act
                $array = $reference->toArray();

                // Assert
                expect($array['address'])->toHaveKey('referenceParameters');
            });

            test('converts to array with endpoint metadata', function (): void {
                // Arrange
                $address = new EndpointReference('https://example.com/metadata');
                $address->metadata()->add('http://example.com', 'Version', '2.0');
                $reference = new MetadataReference($address);

                // Act
                $array = $reference->toArray();

                // Assert
                expect($array['address'])->toHaveKey('metadata');
            });
        });
    });

    describe('MetadataExchangePolicy Factory', function (): void {
        describe('Happy Paths', function (): void {
            test('creates GetMetadataSupported assertion', function (): void {
                // Arrange & Act
                $assertion = MetadataExchangePolicy::getMetadataSupported();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'mex:GetMetadataSupported',
                    'namespace' => 'http://schemas.xmlsoap.org/ws/2004/09/mex',
                ]);
            });

            test('creates MetadataExchange assertion', function (): void {
                // Arrange & Act
                $assertion = MetadataExchangePolicy::metadataExchange();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'mex:MetadataExchange',
                    'namespace' => 'http://schemas.xmlsoap.org/ws/2004/09/mex',
                ]);
            });

            test('creates GetMetadataRequest assertion without dialects', function (): void {
                // Arrange & Act
                $assertion = MetadataExchangePolicy::getMetadataRequest();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'mex:GetMetadataRequest',
                    'namespace' => 'http://schemas.xmlsoap.org/ws/2004/09/mex',
                ]);
            });

            test('creates GetMetadataRequest assertion with single dialect', function (): void {
                // Arrange & Act
                $assertion = MetadataExchangePolicy::getMetadataRequest([
                    MetadataDialect::WSDL->value,
                ]);

                // Assert
                /** @var array{dialects: array<int, string>} $assertion */
                expect($assertion)->toHaveKey('dialects')
                    ->and($assertion['dialects'])->toHaveCount(1)
                    ->and($assertion['dialects'][0])->toBe('http://schemas.xmlsoap.org/wsdl/');
            });

            test('creates GetMetadataRequest assertion with multiple dialects', function (): void {
                // Arrange & Act
                $assertion = MetadataExchangePolicy::getMetadataRequest([
                    MetadataDialect::WSDL->value,
                    MetadataDialect::XmlSchema->value,
                    MetadataDialect::Policy->value,
                ]);

                // Assert
                expect($assertion)->toHaveKey('dialects')
                    ->and($assertion['dialects'])->toHaveCount(3)
                    ->and($assertion['dialects'])->toContain('http://schemas.xmlsoap.org/wsdl/')
                    ->and($assertion['dialects'])->toContain('http://www.w3.org/2001/XMLSchema')
                    ->and($assertion['dialects'])->toContain('http://schemas.xmlsoap.org/ws/2004/09/policy');
            });
        });
    });

    describe('Integration', function (): void {
        describe('Happy Paths', function (): void {
            test('complete metadata exchange workflow', function (): void {
                // Arrange - Create a GetMetadata request
                $request = new GetMetadata(MetadataDialect::WSDL, 'urn:example:service:v1');

                // Create metadata response with multiple sections
                $wsdlSection = new MetadataSection(
                    MetadataDialect::WSDL->value,
                    '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/">...</definitions>',
                    'urn:example:service:v1',
                );

                $schemaSection = new MetadataSection(
                    MetadataDialect::XmlSchema->value,
                    '<schema xmlns="http://www.w3.org/2001/XMLSchema">...</schema>',
                );

                $policySection = new MetadataSection(
                    MetadataDialect::Policy->value,
                    ['type' => 'wsp:Policy', 'assertions' => []],
                );

                // Act
                $metadata = new Metadata();
                $metadata->addSection($wsdlSection)
                    ->addSection($schemaSection)
                    ->addSection($policySection);

                $requestArray = $request->toArray();
                $responseArray = $metadata->toArray();

                // Assert
                /** @var array{metadataSections: array<int, array<string, mixed>>} $responseArray */
                expect($requestArray['dialect'])->toBe('http://schemas.xmlsoap.org/wsdl/')
                    ->and($requestArray['identifier'])->toBe('urn:example:service:v1')
                    ->and($responseArray['metadataSections'])->toHaveCount(3)
                    ->and($responseArray['metadataSections'][0]['identifier'])->toBe('urn:example:service:v1');
            });

            test('metadata reference with complete endpoint configuration', function (): void {
                // Arrange
                $address = new EndpointReference('https://metadata.example.com/service');
                $address->referenceParameters()
                    ->parameter('http://example.com', 'TenantId', 'tenant123')
                    ->parameter('http://example.com', 'Environment', 'production');
                $address->metadata()
                    ->add('http://example.com', 'Version', '2.0')
                    ->add('http://example.com', 'Protocol', 'WS-MetadataExchange');

                // Act
                $reference = new MetadataReference($address);
                $reference->addReferenceProperty('format', 'wsdl')
                    ->addReferenceProperty('language', 'en-US');

                $array = $reference->toArray();

                // Assert
                /** @var array{address: array<string, mixed>, referenceProperties: array<string, mixed>} $array */
                expect($array['address']['address'])->toBe('https://metadata.example.com/service')
                    ->and($array['address'])->toHaveKey('referenceParameters')
                    ->and($array['address'])->toHaveKey('metadata')
                    ->and($array)->toHaveKey('referenceProperties')
                    ->and($array['referenceProperties'])->toHaveCount(2);
            });

            test('advertise metadata exchange capability via policy', function (): void {
                // Arrange & Act - Create policy assertions for MEX capability
                $getMetadataSupported = MetadataExchangePolicy::getMetadataSupported();
                $metadataExchange = MetadataExchangePolicy::metadataExchange();
                $getMetadataRequest = MetadataExchangePolicy::getMetadataRequest([
                    MetadataDialect::WSDL->value,
                    MetadataDialect::XmlSchema->value,
                    MetadataDialect::Policy->value,
                ]);

                // Assert - Verify policy assertions are correctly formed
                expect($getMetadataSupported['type'])->toBe('mex:GetMetadataSupported')
                    ->and($metadataExchange['type'])->toBe('mex:MetadataExchange')
                    ->and($getMetadataRequest['type'])->toBe('mex:GetMetadataRequest')
                    ->and($getMetadataRequest['dialects'])->toHaveCount(3);
            });

            test('full metadata exchange scenario with all dialects', function (): void {
                // Arrange - Service advertises MEX capability
                $capability = MetadataExchangePolicy::getMetadataRequest([
                    MetadataDialect::WSDL->value,
                    MetadataDialect::XmlSchema->value,
                    MetadataDialect::Policy->value,
                    MetadataDialect::MEX->value,
                ]);

                // Client requests metadata
                $request = new GetMetadata(MetadataDialect::WSDL);

                // Service responds with metadata
                $metadata = new Metadata();
                $metadata->addSection(new MetadataSection(
                    MetadataDialect::WSDL->value,
                    '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/">...</definitions>',
                ))
                ->addSection(new MetadataSection(
                    MetadataDialect::XmlSchema->value,
                    '<schema xmlns="http://www.w3.org/2001/XMLSchema">...</schema>',
                ))
                ->addSection(new MetadataSection(
                    MetadataDialect::Policy->value,
                    ['type' => 'wsp:Policy'],
                ));

                // Additional metadata available via reference
                $mexAddress = new EndpointReference('https://metadata.example.com/extended');
                $mexReference = new MetadataReference($mexAddress);
                $metadata->addSection(new MetadataSection(
                    MetadataDialect::MEX->value,
                    $mexReference->toArray(),
                ));

                // Act
                $capabilityArray = $capability;
                $requestArray = $request->toArray();
                $responseArray = $metadata->toArray();

                // Assert
                /** @var array{metadataSections: array<int, array<string, mixed>>} $responseArray */
                expect($capabilityArray['dialects'])->toHaveCount(4)
                    ->and($requestArray['dialect'])->toBe(MetadataDialect::WSDL->value)
                    ->and($responseArray['metadataSections'])->toHaveCount(4)
                    ->and($responseArray['metadataSections'][0]['dialect'])->toBe(MetadataDialect::WSDL->value)
                    ->and($responseArray['metadataSections'][1]['dialect'])->toBe(MetadataDialect::XmlSchema->value)
                    ->and($responseArray['metadataSections'][2]['dialect'])->toBe(MetadataDialect::Policy->value)
                    ->and($responseArray['metadataSections'][3]['dialect'])->toBe(MetadataDialect::MEX->value)
                    ->and($responseArray['metadataSections'][3]['content'])->toHaveKey('address');
            });
        });
    });
});
