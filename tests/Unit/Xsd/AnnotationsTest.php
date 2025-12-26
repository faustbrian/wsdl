<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Annotations\Annotation;
use Cline\WsdlBuilder\Xsd\Annotations\AppInfo;

describe('AppInfo', function (): void {
    describe('Happy Paths', function (): void {
        test('creates appinfo with content', function (): void {
            $appInfo = new AppInfo('machine-readable content');

            expect($appInfo->content)->toBe('machine-readable content');
        });

        test('creates appinfo with content and source', function (): void {
            $appInfo = new AppInfo('metadata', 'http://example.com/schema');

            expect($appInfo->content)->toBe('metadata')
                ->and($appInfo->source)->toBe('http://example.com/schema');
        });

        test('creates appinfo with null source by default', function (): void {
            $appInfo = new AppInfo('content only');

            expect($appInfo->source)->toBeNull();
        });

        test('has readonly properties', function (): void {
            $appInfo = new AppInfo('test content', 'test source');

            expect($appInfo)
                ->toBeInstanceOf(AppInfo::class)
                ->and($appInfo->content)->toBe('test content')
                ->and($appInfo->source)->toBe('test source');
        });
    });

    describe('Edge Cases', function (): void {
        test('creates appinfo with empty string content', function (): void {
            $appInfo = new AppInfo('');

            expect($appInfo->content)->toBe('');
        });

        test('creates appinfo with xml content', function (): void {
            $appInfo = new AppInfo('<metadata><key>value</key></metadata>');

            expect($appInfo->content)->toBe('<metadata><key>value</key></metadata>');
        });

        test('creates appinfo with json content', function (): void {
            $appInfo = new AppInfo('{"key": "value", "nested": {"data": true}}');

            expect($appInfo->content)->toContain('"key": "value"')
                ->and($appInfo->content)->toContain('"nested"');
        });

        test('creates appinfo with unicode content', function (): void {
            $appInfo = new AppInfo('日本語コンテンツ', 'http://example.jp/schema');

            expect($appInfo->content)->toBe('日本語コンテンツ')
                ->and($appInfo->source)->toBe('http://example.jp/schema');
        });

        test('creates appinfo with multiline content', function (): void {
            $content = "line 1\nline 2\nline 3";
            $appInfo = new AppInfo($content);

            expect($appInfo->content)->toBe($content)
                ->and($appInfo->content)->toContain("\n");
        });
    });
});

describe('Annotation', function (): void {
    describe('Happy Paths', function (): void {
        test('creates annotation with parent ComplexType', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('Person');

            $annotation = $complexType->annotation();

            expect($annotation)->toBeInstanceOf(Annotation::class);
        });

        test('adds single documentation to annotation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('Person entity documentation');

            $docs = $annotation->getDocumentations();

            expect($docs)->toHaveCount(1)
                ->and($docs[0])->toBeInstanceOf(Documentation::class)
                ->and($docs[0]->content)->toBe('Person entity documentation');
        });

        test('adds documentation with language and source', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('English description', 'en', 'http://example.com/docs');

            $docs = $annotation->getDocumentations();

            expect($docs[0]->content)->toBe('English description')
                ->and($docs[0]->lang)->toBe('en')
                ->and($docs[0]->source)->toBe('http://example.com/docs');
        });

        test('adds single appinfo to annotation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->appInfo('machine-readable metadata');

            $appInfos = $annotation->getAppInfos();

            expect($appInfos)->toHaveCount(1)
                ->and($appInfos[0])->toBeInstanceOf(AppInfo::class)
                ->and($appInfos[0]->content)->toBe('machine-readable metadata');
        });

        test('adds appinfo with source', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->appInfo('{"version": "1.0"}', 'http://example.com/metadata');

            $appInfos = $annotation->getAppInfos();

            expect($appInfos[0]->content)->toBe('{"version": "1.0"}')
                ->and($appInfos[0]->source)->toBe('http://example.com/metadata');
        });

        test('adds multiple documentations to annotation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('English description', 'en')
                ->documentation('Descripción en español', 'es')
                ->documentation('Description française', 'fr');

            $docs = $annotation->getDocumentations();

            expect($docs)->toHaveCount(3)
                ->and($docs[0]->content)->toBe('English description')
                ->and($docs[0]->lang)->toBe('en')
                ->and($docs[1]->content)->toBe('Descripción en español')
                ->and($docs[1]->lang)->toBe('es')
                ->and($docs[2]->content)->toBe('Description française')
                ->and($docs[2]->lang)->toBe('fr');
        });

        test('adds multiple appinfos to annotation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->appInfo('{"version": "1.0"}')
                ->appInfo('<metadata><author>John</author></metadata>')
                ->appInfo('custom data');

            $appInfos = $annotation->getAppInfos();

            expect($appInfos)->toHaveCount(3)
                ->and($appInfos[0]->content)->toBe('{"version": "1.0"}')
                ->and($appInfos[1]->content)->toBe('<metadata><author>John</author></metadata>')
                ->and($appInfos[2]->content)->toBe('custom data');
        });

        test('end returns parent ComplexType', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('Person');

            $result = $complexType->annotation()
                ->documentation('test')
                ->end();

            expect($result)->toBe($complexType);
        });

        test('fluent interface chains all annotation methods', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('English docs', 'en', 'http://example.com/en')
                ->documentation('Spanish docs', 'es')
                ->appInfo('{"version": "1.0"}', 'http://example.com/meta')
                ->appInfo('extra metadata');

            expect($annotation)->toBeInstanceOf(Annotation::class)
                ->and($annotation->getDocumentations())->toHaveCount(2)
                ->and($annotation->getAppInfos())->toHaveCount(2);
        });
    });

    describe('Default Values', function (): void {
        test('has empty documentations array by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation();

            expect($annotation->getDocumentations())->toBe([]);
        });

        test('has empty appinfos array by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation();

            expect($annotation->getAppInfos())->toBe([]);
        });
    });

    describe('Integration', function (): void {
        test('uses annotation with ComplexType via fluent API', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $complexType = $wsdl->complexType('Person')
                ->annotation()
                    ->documentation('Represents a person entity')
                    ->appInfo('{"table": "persons"}')
                    ->end()
                ->element('name', 'xsd:string')
                ->element('age', 'xsd:int');

            expect($complexType->getName())->toBe('Person')
                ->and($complexType->getElements())->toHaveCount(2);

            $annotation = $complexType->annotation();
            expect($annotation->getDocumentations())->toHaveCount(1)
                ->and($annotation->getAppInfos())->toHaveCount(1);
        });

        test('reuses existing annotation instance when called multiple times', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('Person');

            $annotation1 = $complexType->annotation()
                ->documentation('First documentation');

            $annotation2 = $complexType->annotation()
                ->documentation('Second documentation');

            expect($annotation1)->toBe($annotation2)
                ->and($annotation1->getDocumentations())->toHaveCount(2);
        });

        test('combines documentation and appinfo in complex workflow', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $complexType = $wsdl->complexType('Order')
                ->annotation()
                    ->documentation('Order entity for e-commerce', 'en', 'http://example.com/docs/order')
                    ->documentation('Entidad de pedido para comercio electrónico', 'es')
                    ->appInfo('{"table": "orders", "version": "2.0"}', 'http://example.com/db')
                    ->appInfo('<validation><required>customer_id</required></validation>')
                    ->end()
                ->element('orderId', 'xsd:string')
                ->element('customerId', 'xsd:string')
                ->element('total', 'xsd:decimal');

            $annotation = $complexType->annotation();

            expect($annotation->getDocumentations())->toHaveCount(2)
                ->and($annotation->getDocumentations()[0]->content)->toBe('Order entity for e-commerce')
                ->and($annotation->getDocumentations()[1]->lang)->toBe('es')
                ->and($annotation->getAppInfos())->toHaveCount(2)
                ->and($annotation->getAppInfos()[0]->content)->toContain('"table": "orders"')
                ->and($annotation->getAppInfos()[1]->content)->toContain('<validation>');
        });
    });

    describe('Edge Cases', function (): void {
        test('handles annotation with empty documentation content', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('');

            $docs = $annotation->getDocumentations();

            expect($docs)->toHaveCount(1)
                ->and($docs[0]->content)->toBe('');
        });

        test('handles annotation with empty appinfo content', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->appInfo('');

            $appInfos = $annotation->getAppInfos();

            expect($appInfos)->toHaveCount(1)
                ->and($appInfos[0]->content)->toBe('');
        });

        test('handles annotation with unicode characters in documentation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('人物实体描述', 'zh')
                ->documentation('شخص کی تفصیل', 'ur')
                ->documentation('Описание персоны', 'ru');

            $docs = $annotation->getDocumentations();

            expect($docs)->toHaveCount(3)
                ->and($docs[0]->content)->toBe('人物实体描述')
                ->and($docs[1]->content)->toBe('شخص کی تفصیل')
                ->and($docs[2]->content)->toBe('Описание персоны');
        });

        test('handles annotation with xml content in appinfo', function (): void {
            $xmlContent = '<schema xmlns="http://www.w3.org/2001/XMLSchema"><element name="test"/></schema>';
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->appInfo($xmlContent);

            $appInfos = $annotation->getAppInfos();

            expect($appInfos[0]->content)->toBe($xmlContent)
                ->and($appInfos[0]->content)->toContain('xmlns=');
        });

        test('handles annotation with very long documentation content', function (): void {
            $longContent = str_repeat('This is a very long documentation string. ', 100);
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation($longContent);

            $docs = $annotation->getDocumentations();

            expect($docs[0]->content)->toBe($longContent)
                ->and(strlen($docs[0]->content))->toBeGreaterThan(1000);
        });

        test('handles annotation with special characters in source urls', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('test', 'en', 'http://example.com/docs?version=1.0&lang=en')
                ->appInfo('metadata', 'http://example.com/meta#section-1');

            $docs = $annotation->getDocumentations();
            $appInfos = $annotation->getAppInfos();

            expect($docs[0]->source)->toBe('http://example.com/docs?version=1.0&lang=en')
                ->and($appInfos[0]->source)->toBe('http://example.com/meta#section-1');
        });

        test('handles annotation with multiline documentation content', function (): void {
            $multilineContent = "Line 1: Introduction\nLine 2: Details\nLine 3: Conclusion";
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation($multilineContent);

            $docs = $annotation->getDocumentations();

            expect($docs[0]->content)->toBe($multilineContent)
                ->and($docs[0]->content)->toContain("\n");
        });

        test('preserves order of mixed documentation and appinfo additions', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $annotation = $wsdl->complexType('Person')->annotation()
                ->documentation('Doc 1')
                ->appInfo('AppInfo 1')
                ->documentation('Doc 2')
                ->appInfo('AppInfo 2')
                ->documentation('Doc 3');

            expect($annotation->getDocumentations())->toHaveCount(3)
                ->and($annotation->getAppInfos())->toHaveCount(2)
                ->and($annotation->getDocumentations()[0]->content)->toBe('Doc 1')
                ->and($annotation->getDocumentations()[1]->content)->toBe('Doc 2')
                ->and($annotation->getDocumentations()[2]->content)->toBe('Doc 3')
                ->and($annotation->getAppInfos()[0]->content)->toBe('AppInfo 1')
                ->and($annotation->getAppInfos()[1]->content)->toBe('AppInfo 2');
        });
    });
});
