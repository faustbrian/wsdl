<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Mtom\Enums\ContentTransferEncoding;
use Cline\WsdlBuilder\WsExtensions\Mtom\MtomPolicy;
use Cline\WsdlBuilder\WsExtensions\Mtom\XopInclude;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyOperator;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;

describe('MTOM/XOP Support', function (): void {
    describe('MtomPolicy', function (): void {
        describe('Happy Paths', function (): void {
            test('creates optimized MIME serialization assertion', function (): void {
                // Arrange & Act
                $assertion = MtomPolicy::optimizedMimeSerialization();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wsoma:OptimizedMimeSerialization',
                    'namespace' => MtomPolicy::NAMESPACE_URI,
                ])
                    ->and($assertion)->toHaveKey('type')
                    ->and($assertion)->toHaveKey('namespace')
                    ->and($assertion['namespace'])->toBe('http://schemas.xmlsoap.org/ws/2004/09/policy/optimizedmimeserialization');
            });

            test('namespace URI constant is correct', function (): void {
                // Arrange & Act & Assert
                expect(MtomPolicy::NAMESPACE_URI)->toBe('http://schemas.xmlsoap.org/ws/2004/09/policy/optimizedmimeserialization');
            });

            test('assertion type uses wsoma prefix', function (): void {
                // Arrange & Act
                $assertion = MtomPolicy::optimizedMimeSerialization();

                // Assert
                expect($assertion['type'])->toStartWith('wsoma:')
                    ->and($assertion['type'])->toBe('wsoma:OptimizedMimeSerialization');
            });
        });
    });

    describe('XopInclude', function (): void {
        describe('Happy Paths', function (): void {
            test('creates XopInclude with href', function (): void {
                // Arrange & Act
                $xopInclude = new XopInclude('cid:example@example.org');

                // Assert
                expect($xopInclude->href)->toBe('cid:example@example.org');
            });

            test('creates XopInclude using static factory method', function (): void {
                // Arrange & Act
                $xopInclude = XopInclude::create('cid:binary-data@soap.example.com');

                // Assert
                expect($xopInclude)->toBeInstanceOf(XopInclude::class)
                    ->and($xopInclude->href)->toBe('cid:binary-data@soap.example.com');
            });

            test('namespace URI constant is correct', function (): void {
                // Arrange & Act & Assert
                expect(XopInclude::NAMESPACE_URI)->toBe('http://www.w3.org/2004/08/xop/include');
            });

            test('supports standard cid URL format', function (): void {
                // Arrange & Act
                $xopInclude = XopInclude::create('cid:attachment123@soap.example.com');

                // Assert
                expect($xopInclude->href)->toStartWith('cid:')
                    ->and($xopInclude->href)->toContain('@');
            });

            test('is readonly class', function (): void {
                // Arrange
                $xopInclude = new XopInclude('cid:test@example.com');

                // Act & Assert
                $reflection = new ReflectionClass($xopInclude);
                expect($reflection->isReadOnly())->toBeTrue();
            });
        });
    });

    describe('ContentTransferEncoding', function (): void {
        describe('Happy Paths', function (): void {
            test('provides Base64 encoding value', function (): void {
                // Arrange & Act
                $encoding = ContentTransferEncoding::Base64;

                // Assert
                expect($encoding->value)->toBe('base64');
            });

            test('provides Binary encoding value', function (): void {
                // Arrange & Act
                $encoding = ContentTransferEncoding::Binary;

                // Assert
                expect($encoding->value)->toBe('binary');
            });

            test('provides QuotedPrintable encoding value', function (): void {
                // Arrange & Act
                $encoding = ContentTransferEncoding::QuotedPrintable;

                // Assert
                expect($encoding->value)->toBe('quoted-printable');
            });

            test('provides 8bit encoding value', function (): void {
                // Arrange & Act
                $encoding = ContentTransferEncoding::EightBit;

                // Assert
                expect($encoding->value)->toBe('8bit');
            });

            test('provides 7bit encoding value', function (): void {
                // Arrange & Act
                $encoding = ContentTransferEncoding::SevenBit;

                // Assert
                expect($encoding->value)->toBe('7bit');
            });

            test('enum contains all five encoding values', function (): void {
                // Arrange & Act
                $encodings = ContentTransferEncoding::cases();

                // Assert
                expect($encodings)->toHaveCount(5)
                    ->and($encodings)->toContain(ContentTransferEncoding::Base64)
                    ->and($encodings)->toContain(ContentTransferEncoding::Binary)
                    ->and($encodings)->toContain(ContentTransferEncoding::QuotedPrintable)
                    ->and($encodings)->toContain(ContentTransferEncoding::EightBit)
                    ->and($encodings)->toContain(ContentTransferEncoding::SevenBit);
            });
        });
    });

    describe('XsdType swaRef support', function (): void {
        describe('Happy Paths', function (): void {
            test('provides swaRef type for SOAP attachments', function (): void {
                // Arrange & Act
                $type = XsdType::SwaRef;

                // Assert
                expect($type->value)->toBe('swaRef');
            });

            test('swaRef type is available in enum', function (): void {
                // Arrange & Act
                $types = XsdType::cases();

                // Assert
                expect($types)->toContain(XsdType::SwaRef);
            });

            test('swaRef can be used in type definitions', function (): void {
                // Arrange
                $wsdl = Wsdl::create('AttachmentService', 'http://test.example.com/');

                // Act
                $complexType = $wsdl->complexType('Document')
                    ->element('fileName', XsdType::String)
                    ->element('content', XsdType::SwaRef);

                // Assert
                expect($complexType)->toBeInstanceOf(ComplexType::class)
                    ->and($wsdl->getComplexTypes())->toHaveCount(1);
            });
        });
    });

    describe('MTOM policy integration with WS-Policy', function (): void {
        describe('Happy Paths', function (): void {
            test('adds MTOM policy to WSDL', function (): void {
                // Arrange
                $wsdl = Wsdl::create('MtomService', 'http://test.example.com/');

                // Act
                $policy = $wsdl->policy('MtomPolicy')
                    ->all()
                    ->assertion(
                        MtomPolicy::NAMESPACE_URI,
                        'wsoma:OptimizedMimeSerialization',
                    );

                // Assert
                expect($wsdl->getPolicies())->toHaveCount(1)
                    ->and($policy)->toBeInstanceOf(PolicyOperator::class);
            });

            test('adds MTOM policy to binding', function (): void {
                // Arrange
                $wsdl = Wsdl::create('MtomService', 'http://test.example.com/');
                $binding = $wsdl->binding('MtomBinding', 'MtomPortType');

                // Act
                $policy = $binding->policy('MtomBindingPolicy')
                    ->all()
                    ->assertion(
                        MtomPolicy::NAMESPACE_URI,
                        'wsoma:OptimizedMimeSerialization',
                    );

                // Assert
                expect($binding->getPolicies())->toHaveCount(1)
                    ->and($policy)->toBeInstanceOf(PolicyOperator::class);
            });

            test('creates MTOM assertion using factory method', function (): void {
                // Arrange
                $wsdl = Wsdl::create('MtomService', 'http://test.example.com/');
                $mtomAssertion = MtomPolicy::optimizedMimeSerialization();

                // Act
                $policy = $wsdl->policy('MtomPolicy')
                    ->all()
                    ->assertion(
                        $mtomAssertion['namespace'],
                        $mtomAssertion['type'],
                    );

                // Assert
                expect($wsdl->getPolicies())->toHaveCount(1)
                    ->and($policy)->toBeInstanceOf(PolicyOperator::class);
            });
        });
    });

    describe('MTOM-enabled WSDL generation', function (): void {
        describe('Happy Paths', function (): void {
            test('generates WSDL with MTOM policy', function (): void {
                // Arrange
                $wsdl = Wsdl::create('DocumentService', 'http://example.com/documents');

                // Add MTOM policy
                $wsdl->policy('MtomPolicy')
                    ->all()
                    ->assertion(
                        MtomPolicy::NAMESPACE_URI,
                        'wsoma:OptimizedMimeSerialization',
                    );

                // Define types
                $wsdl->complexType('Document')
                    ->element('id', XsdType::String)
                    ->element('fileName', XsdType::String)
                    ->element('binaryContent', XsdType::Base64Binary);

                // Define messages
                $wsdl->message('UploadDocumentRequest')
                    ->part('document', 'tns:Document');

                $wsdl->message('UploadDocumentResponse')
                    ->part('success', XsdType::Boolean);

                // Define port type
                $wsdl->portType('DocumentPortType')
                    ->operation('UploadDocument', 'UploadDocumentRequest', 'UploadDocumentResponse');

                // Define binding with MTOM policy
                $binding = $wsdl->binding('DocumentBinding', 'DocumentPortType')
                    ->operation('UploadDocument', 'http://example.com/documents/upload');

                $binding->policy('MtomBindingPolicy')
                    ->all()
                    ->assertion(
                        MtomPolicy::NAMESPACE_URI,
                        'wsoma:OptimizedMimeSerialization',
                    );

                // Define service
                $wsdl->service('DocumentService')
                    ->port('DocumentPort', 'DocumentBinding', 'http://example.com/documents');

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)->toBeString()
                    ->and($xml)->toContain('wsp:Policy')
                    ->and($xml)->toContain('MtomPolicy')
                    ->and($xml)->toContain('wsoma:OptimizedMimeSerialization')
                    ->and($xml)->toContain('xmlns:wsp')
                    ->and($xml)->toContain('wsdl:definitions')
                    ->and($xml)->toContain('DocumentService');
            });

            test('generates WSDL with binary content type using base64Binary', function (): void {
                // Arrange
                $wsdl = Wsdl::create('BinaryService', 'http://example.com/binary');

                $wsdl->complexType('BinaryData')
                    ->element('data', XsdType::Base64Binary);

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)->toBeString()
                    ->and($xml)->toContain('xsd:base64Binary');
            });

            test('generates WSDL with swaRef attachment reference', function (): void {
                // Arrange
                $wsdl = Wsdl::create('AttachmentService', 'http://example.com/attachments');

                $wsdl->complexType('AttachmentRef')
                    ->element('attachmentId', XsdType::String)
                    ->element('attachmentRef', XsdType::SwaRef);

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)->toBeString()
                    ->and($xml)->toContain('swaRef');
            });

            test('generates complete MTOM-enabled service WSDL', function (): void {
                // Arrange
                $wsdl = Wsdl::create('ImageService', 'http://example.com/images');

                // MTOM policy at WSDL level
                $wsdl->policy('ImageServiceMtomPolicy')
                    ->exactlyOne()
                    ->all()
                    ->assertion(
                        MtomPolicy::NAMESPACE_URI,
                        'wsoma:OptimizedMimeSerialization',
                    );

                // Image type with binary content
                $wsdl->complexType('Image')
                    ->element('imageId', XsdType::String)
                    ->element('imageName', XsdType::String)
                    ->element('imageFormat', XsdType::String)
                    ->element('imageData', XsdType::Base64Binary);

                // Upload request/response
                $wsdl->message('UploadImageRequest')
                    ->part('image', 'tns:Image');

                $wsdl->message('UploadImageResponse')
                    ->part('imageId', XsdType::String);

                // Download request/response
                $wsdl->message('DownloadImageRequest')
                    ->part('imageId', XsdType::String);

                $wsdl->message('DownloadImageResponse')
                    ->part('image', 'tns:Image');

                // Port type
                $portType = $wsdl->portType('ImagePortType');

                $portType->operation('UploadImage', 'UploadImageRequest', 'UploadImageResponse');
                $portType->operation('DownloadImage', 'DownloadImageRequest', 'DownloadImageResponse');

                // Binding
                $binding = $wsdl->binding('ImageBinding', 'ImagePortType');
                $binding->operation('UploadImage', 'http://example.com/images/upload');
                $binding->operation('DownloadImage', 'http://example.com/images/download');

                // Service
                $wsdl->service('ImageService')
                    ->port('ImagePort', 'ImageBinding', 'http://example.com/images/endpoint');

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)->toBeString()
                    ->and($xml)->toContain('wsp:Policy')
                    ->and($xml)->toContain('ImageServiceMtomPolicy')
                    ->and($xml)->toContain('wsoma:OptimizedMimeSerialization')
                    ->and($xml)->toContain('wsp:ExactlyOne')
                    ->and($xml)->toContain('wsp:All')
                    ->and($xml)->toContain('ImageService')
                    ->and($xml)->toContain('ImagePortType')
                    ->and($xml)->toContain('ImageBinding')
                    ->and($xml)->toContain('UploadImage')
                    ->and($xml)->toContain('DownloadImage')
                    ->and($xml)->toContain('xsd:base64Binary');
            });
        });
    });
});
