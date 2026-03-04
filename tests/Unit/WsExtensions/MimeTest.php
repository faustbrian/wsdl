<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsdlGenerator;
use Cline\WsdlBuilder\WsExtensions\Mime\MimeContent;
use Cline\WsdlBuilder\WsExtensions\Mime\MimeMultipartRelated;
use Cline\WsdlBuilder\WsExtensions\Mime\MimePart;
use Cline\WsdlBuilder\WsExtensions\Mime\MimeXml;

describe('MIME', function (): void {
    describe('MimeContent', function (): void {
        describe('Happy Paths', function (): void {
            test('creates mime content with part and type', function (): void {
                // Arrange & Act
                $content = new MimeContent('attachment', 'application/octet-stream');

                // Assert
                expect($content->getPart())->toBe('attachment')
                    ->and($content->getType())->toBe('application/octet-stream');
            });

            test('creates mime content without part and type', function (): void {
                // Arrange & Act
                $content = new MimeContent();

                // Assert
                expect($content->getPart())->toBeNull()
                    ->and($content->getType())->toBeNull();
            });

            test('creates mime content using static create method', function (): void {
                // Arrange & Act
                $content = MimeContent::create('document', 'text/xml');

                // Assert
                expect($content)->toBeInstanceOf(MimeContent::class)
                    ->and($content->getPart())->toBe('document')
                    ->and($content->getType())->toBe('text/xml');
            });

            test('mime namespace constant is correct', function (): void {
                // Arrange & Act & Assert
                expect(MimeContent::MIME_NS)->toBe('http://schemas.xmlsoap.org/wsdl/mime/');
            });
        });
    });

    describe('MimePart', function (): void {
        describe('Happy Paths', function (): void {
            test('creates mime part with name', function (): void {
                // Arrange & Act
                $part = new MimePart('attachment');

                // Assert
                expect($part->getName())->toBe('attachment')
                    ->and($part->getMimeContent())->toBeNull()
                    ->and($part->hasSoapBody())->toBeFalse();
            });

            test('creates mime part without name', function (): void {
                // Arrange & Act
                $part = new MimePart();

                // Assert
                expect($part->getName())->toBeNull();
            });

            test('sets mime content using content method', function (): void {
                // Arrange
                $part = new MimePart();

                // Act
                $result = $part->content('file', 'application/pdf');

                // Assert
                expect($result)->toBe($part)
                    ->and($part->getMimeContent())->toBeInstanceOf(MimeContent::class)
                    ->and($part->getMimeContent()->getPart())->toBe('file')
                    ->and($part->getMimeContent()->getType())->toBe('application/pdf');
            });

            test('sets mime content using setMimeContent method', function (): void {
                // Arrange
                $part = new MimePart();
                $content = new MimeContent('data', 'application/json');

                // Act
                $result = $part->setMimeContent($content);

                // Assert
                expect($result)->toBe($part)
                    ->and($part->getMimeContent())->toBe($content);
            });

            test('sets soap body reference', function (): void {
                // Arrange
                $part = new MimePart();

                // Act
                $result = $part->soapBody();

                // Assert
                expect($result)->toBe($part)
                    ->and($part->hasSoapBody())->toBeTrue();
            });

            test('unsets soap body reference', function (): void {
                // Arrange
                $part = new MimePart();
                $part->soapBody();

                // Act
                $result = $part->soapBody(false);

                // Assert
                expect($result)->toBe($part)
                    ->and($part->hasSoapBody())->toBeFalse();
            });

            test('end returns parent object', function (): void {
                // Arrange
                $parent = new stdClass();
                $part = new MimePart('test', $parent);

                // Act
                $result = $part->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('end returns null when no parent', function (): void {
                // Arrange
                $part = new MimePart();

                // Act
                $result = $part->end();

                // Assert
                expect($result)->toBeNull();
            });

            test('creates mime part using static create method', function (): void {
                // Arrange & Act
                $part = MimePart::create('attachment');

                // Assert
                expect($part)->toBeInstanceOf(MimePart::class)
                    ->and($part->getName())->toBe('attachment');
            });
        });
    });

    describe('MimeMultipartRelated', function (): void {
        describe('Happy Paths', function (): void {
            test('creates empty multipart related', function (): void {
                // Arrange & Act
                $multipart = new MimeMultipartRelated();

                // Assert
                expect($multipart->getParts())->toBeArray()
                    ->and($multipart->getParts())->toHaveCount(0);
            });

            test('adds part using part method', function (): void {
                // Arrange
                $multipart = new MimeMultipartRelated();

                // Act
                $part = $multipart->part('attachment');

                // Assert
                expect($part)->toBeInstanceOf(MimePart::class)
                    ->and($part->getName())->toBe('attachment')
                    ->and($multipart->getParts())->toHaveCount(1)
                    ->and($multipart->getParts()[0])->toBe($part);
            });

            test('adds multiple parts', function (): void {
                // Arrange
                $multipart = new MimeMultipartRelated();

                // Act
                $part1 = $multipart->part('attachment1');
                $part2 = $multipart->part('attachment2');

                // Assert
                expect($multipart->getParts())->toHaveCount(2)
                    ->and($multipart->getParts()[0])->toBe($part1)
                    ->and($multipart->getParts()[1])->toBe($part2);
            });

            test('adds existing part using addPart method', function (): void {
                // Arrange
                $multipart = new MimeMultipartRelated();
                $part = new MimePart('file');

                // Act
                $result = $multipart->addPart($part);

                // Assert
                expect($result)->toBe($multipart)
                    ->and($multipart->getParts())->toHaveCount(1)
                    ->and($multipart->getParts()[0])->toBe($part);
            });

            test('part method returns chained part for fluent interface', function (): void {
                // Arrange
                $multipart = new MimeMultipartRelated();

                // Act
                $part = $multipart->part()
                    ->content('data', 'application/json')
                    ->soapBody();

                // Assert
                expect($part->getMimeContent())->toBeInstanceOf(MimeContent::class)
                    ->and($part->hasSoapBody())->toBeTrue();
            });

            test('end returns parent object', function (): void {
                // Arrange
                $parent = new stdClass();
                $multipart = new MimeMultipartRelated($parent);

                // Act
                $result = $multipart->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('end returns null when no parent', function (): void {
                // Arrange
                $multipart = new MimeMultipartRelated();

                // Act
                $result = $multipart->end();

                // Assert
                expect($result)->toBeNull();
            });

            test('creates multipart using static create method', function (): void {
                // Arrange & Act
                $multipart = MimeMultipartRelated::create();

                // Assert
                expect($multipart)->toBeInstanceOf(MimeMultipartRelated::class);
            });
        });
    });

    describe('MimeXml', function (): void {
        describe('Happy Paths', function (): void {
            test('creates mime xml with part', function (): void {
                // Arrange & Act
                $mimeXml = new MimeXml('xmlData');

                // Assert
                expect($mimeXml->getPart())->toBe('xmlData');
            });

            test('creates mime xml without part', function (): void {
                // Arrange & Act
                $mimeXml = new MimeXml();

                // Assert
                expect($mimeXml->getPart())->toBeNull();
            });

            test('creates mime xml using static create method', function (): void {
                // Arrange & Act
                $mimeXml = MimeXml::create('document');

                // Assert
                expect($mimeXml)->toBeInstanceOf(MimeXml::class)
                    ->and($mimeXml->getPart())->toBe('document');
            });
        });
    });

    describe('Integration with WsdlGenerator', function (): void {
        describe('Happy Paths', function (): void {
            test('generates wsdl with mime multipart on binding operation', function (): void {
                // Arrange
                $wsdl = Wsdl::create('FileUploadService', 'http://example.com/fileupload');

                $wsdl->message('UploadFileRequest')
                    ->part('metadata', 'xsd:string')
                    ->part('fileData', 'xsd:base64Binary');

                $wsdl->message('UploadFileResponse')
                    ->part('result', 'xsd:string');

                $wsdl->portType('FileUploadPortType')
                    ->operation('UploadFile', 'UploadFileRequest', 'UploadFileResponse');

                $binding = $wsdl->binding('FileUploadBinding', 'FileUploadPortType')
                    ->operation('UploadFile', 'http://example.com/fileupload/UploadFile');

                $binding->inputMime()
                    ->part()->soapBody()->end()
                    ->part()->content('fileData', 'application/octet-stream');

                // Act
                $generator = new WsdlGenerator($wsdl);
                $xml = $generator->generate();

                // Assert
                expect($xml)->toContain('xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/"')
                    ->and($xml)->toContain('mime:multipartRelated')
                    ->and($xml)->toContain('mime:part')
                    ->and($xml)->toContain('mime:content')
                    ->and($xml)->toContain('part="fileData"')
                    ->and($xml)->toContain('type="application/octet-stream"')
                    ->and($xml)->toContain('soap:body');
            });

            test('generates wsdl without mime namespace when not using mime', function (): void {
                // Arrange
                $wsdl = Wsdl::create('SimpleService', 'http://example.com/simple');

                $wsdl->message('SimpleRequest')
                    ->part('param', 'xsd:string');

                $wsdl->message('SimpleResponse')
                    ->part('result', 'xsd:string');

                $wsdl->portType('SimplePortType')
                    ->operation('SimpleOp', 'SimpleRequest', 'SimpleResponse');

                $wsdl->binding('SimpleBinding', 'SimplePortType')
                    ->operation('SimpleOp', 'http://example.com/simple/SimpleOp');

                // Act
                $generator = new WsdlGenerator($wsdl);
                $xml = $generator->generate();

                // Assert
                expect($xml)->not->toContain('xmlns:mime=')
                    ->and($xml)->not->toContain('<mime:');
            });

            test('generates wsdl with mime on both input and output', function (): void {
                // Arrange
                $wsdl = Wsdl::create('DocumentService', 'http://example.com/doc');

                $wsdl->message('ProcessDocRequest')
                    ->part('metadata', 'xsd:string')
                    ->part('document', 'xsd:base64Binary');

                $wsdl->message('ProcessDocResponse')
                    ->part('result', 'xsd:string')
                    ->part('processedDoc', 'xsd:base64Binary');

                $wsdl->portType('DocumentPortType')
                    ->operation('ProcessDocument', 'ProcessDocRequest', 'ProcessDocResponse');

                $binding = $wsdl->binding('DocumentBinding', 'DocumentPortType')
                    ->operation('ProcessDocument', 'http://example.com/doc/ProcessDocument');

                $binding->inputMime()
                    ->part()->soapBody()->end()
                    ->part()->content('document', 'application/pdf');

                $binding->outputMime()
                    ->part()->soapBody()->end()
                    ->part()->content('processedDoc', 'application/pdf');

                // Act
                $generator = new WsdlGenerator($wsdl);
                $xml = $generator->generate();

                // Assert
                expect($xml)->toContain('xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/"')
                    ->and($xml)->toContain('wsdl:input')
                    ->and($xml)->toContain('wsdl:output');

                // Count mime:multipartRelated occurrences (should be 2: one for input, one for output)
                $count = mb_substr_count($xml, '<mime:multipartRelated');
                expect($count)->toBe(2);
            });

            test('generates mime content with multiple parts', function (): void {
                // Arrange
                $wsdl = Wsdl::create('MultiAttachService', 'http://example.com/multiattach');

                $wsdl->message('MultiAttachRequest')
                    ->part('metadata', 'xsd:string')
                    ->part('file1', 'xsd:base64Binary')
                    ->part('file2', 'xsd:base64Binary')
                    ->part('file3', 'xsd:base64Binary');

                $wsdl->message('MultiAttachResponse')
                    ->part('result', 'xsd:string');

                $wsdl->portType('MultiAttachPortType')
                    ->operation('UploadMultiple', 'MultiAttachRequest', 'MultiAttachResponse');

                $binding = $wsdl->binding('MultiAttachBinding', 'MultiAttachPortType')
                    ->operation('UploadMultiple', 'http://example.com/multiattach/UploadMultiple');

                $binding->inputMime()
                    ->part()->soapBody()->end()
                    ->part()->content('file1', 'image/jpeg')->end()
                    ->part()->content('file2', 'application/pdf')->end()
                    ->part()->content('file3', 'text/xml');

                // Act
                $generator = new WsdlGenerator($wsdl);
                $xml = $generator->generate();

                // Assert
                $partCount = mb_substr_count($xml, '<mime:part');
                expect($partCount)->toBe(4) // 1 for soap:body + 3 for attachments
                    ->and($xml)->toContain('type="image/jpeg"')
                    ->and($xml)->toContain('type="application/pdf"')
                    ->and($xml)->toContain('type="text/xml"');
            });
        });
    });
});
