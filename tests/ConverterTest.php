<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Willtj\PhpDraftjsHtml\Converter;
use Prezly\DraftPhp\Converter as DraftConverter;

class ExampleTest extends TestCase
{
    /**
     * @test
     * @dataProvider standardBlocksProvider
     */
    public function it_converts_standard_blocks_to_html($input, $expected): void
    {
        $contentState = DraftConverter::convertFromJson($input);

        $converter = new Converter;
        $result = $converter
            ->setState($contentState)
            ->toHtml();

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider customBlocksProvider
     */
    public function it_converts_custom_blocks_to_html($styleMap, $input, $expected): void
    {
        $contentState = DraftConverter::convertFromJson($input);

        $converter = new Converter;
        $result = $converter
            ->setState($contentState)
            ->updateStyleMap(json_decode($styleMap, true)['inlineStyles'])
            ->toHtml();

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_handles_nested_lists(): void
    {
        $json = '{"entityMap":{},"blocks":[{"key":"c06lq","text":"A list","type":"unordered-list-item","depth":0,"inlineStyleRanges":[],"entityRanges":[],"data":{}},{"key":"8jie3","text":"With multiple levels","type":"unordered-list-item","depth":1,"inlineStyleRanges":[],"entityRanges":[],"data":{}},{"key":"1kenh","text":"Back to first level","type":"unordered-list-item","depth":0,"inlineStyleRanges":[],"entityRanges":[],"data":{}}]}';

        $contentState = DraftConverter::convertFromJson($json);

        $converter = new Converter;
        $result = $converter
            ->setState($contentState)
            ->toHtml();

        $this->assertEquals('<ul><li>A list<ul><li>With multiple levels</li></ul></li><li>Back to first level</li></ul>', $result);
    }

    // /**
    //  * @test
    //  */
    public function it_handles_multi_level_lists(): void
    {
        $json = '{"entityMap":{},"blocks":[{"key":"c06lq","text":"First level","type":"unordered-list-item","depth":0,"inlineStyleRanges":[],"entityRanges":[],"data":{}},{"key":"8jie3","text":"Second level","type":"unordered-list-item","depth":1,"inlineStyleRanges":[],"entityRanges":[],"data":{}},{"key":"1sr78","text":"Third level","type":"unordered-list-item","depth":2,"inlineStyleRanges":[],"entityRanges":[],"data":{}},{"key":"1kenh","text":"Back to second level","type":"unordered-list-item","depth":1,"inlineStyleRanges":[],"entityRanges":[],"data":{}},{"key":"2fg9q","text":"Back to first level","type":"unordered-list-item","depth":0,"inlineStyleRanges":[],"entityRanges":[],"data":{}}]}';

        $contentState = DraftConverter::convertFromJson($json);

        $converter = new Converter;
        $result = $converter
            ->setState($contentState)
            ->toHtml();

        $this->assertEquals('<ul><li>First level<ul><li>Second level<ul><li>Third level</li></ul></li><li>Back to second level</li></ul></li><li>Back to first level</li></ul>', $result);
    }

    public function standardBlocksProvider(): array
    {
        return [
            // Plain text
            [
                '{"entityMap":{},"blocks":[{"key":"33nh8","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[]}]}',
                '<p>a</p>',
            ],
            // // Single inline style
            [
                '{"entityMap":{},"blocks":[{"key":"99n0j","text":"asdf","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":3,"length":1,"style":"BOLD"}],"entityRanges":[]}]}',
                '<p>asd<strong>f</strong></p>',
            ],
            // Nested inline styles
            [
                '{"entityMap":{},"blocks":[{"key":"9nc73","text":"BoldItalic","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":10,"style":"BOLD"},{"offset":0,"length":10,"style":"ITALIC"}],"entityRanges":[]}]}',
                '<p><em><strong>BoldItalic</strong></em></p>',
            ],
            // Adjacent inline styles
            [
                '{"entityMap":{},"blocks":[{"key":"9nc73","text":"BoldItalic","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":4,"length":6,"style":"BOLD"},{"offset":0,"length":4,"style":"ITALIC"}],"entityRanges":[]}]}',
                '<p><em>Bold</em><strong>Italic</strong></p>',
            ],
            // Entity
            [
                '{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/","rel":null,"title":"hi","extra":"foo"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}',
                '<p><a href="/" title="hi"><em>a</em></a></p>',
            ],
            // Entity with href
            [
                '{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"href":"/","rel":null,"title":"hi","extra":"foo"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}',
                '<p><a href="/" title="hi"><em>a</em></a></p>',
            ],
            // Entity with data-*
            [
                '{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/","rel":null,"title":"hi","extra":"foo","data-id":42,"data-mutability":"mutable","data-False":"bad","data-":"no"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}',
                '<p><a href="/" title="hi" data-id="42" data-mutability="mutable"><em>a</em></a></p>',
            ],
            // Entity with inline style
            [
                '{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}',
                '<p><a href="/"><em>a</em></a></p>',
            ],
            // Ordered list
            [
                '{"entityMap":{},"blocks":[{"key":"33nh8","text":"An ordered list:","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[]},{"key":"8kinl","text":"One","type":"ordered-list-item","depth":0,"inlineStyleRanges":[],"entityRanges":[]},{"key":"ekll4","text":"Two","type":"ordered-list-item","depth":0,"inlineStyleRanges":[],"entityRanges":[]}]}',
                '<p>An ordered list:</p><ol><li>One</li><li>Two</li></ol>',
            ],
        ];
    }

    public function customBlocksProvider(): array
    {
        return [
            // Built-in element using alternate tag
            [
                '{"inlineStyles":{"BOLD":{"element":"b"}}}',
                '{"entityMap":{},"blocks":[{"key":"33nh8","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[]}]}',
                '<p>a</p>',
            ],
            // Built-in element with all fields specified
            [
                '{"inlineStyles":{"ITALIC":{"element":"i","attributes":{"className":"foo"},"style":{"textDecoration":"underline"}}}}',
                '{"entityMap":{},"blocks":[{"key":"99n0j","text":"asdf","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":3,"length":1,"style":"ITALIC"}],"entityRanges":[]}]}',
                '<p>asd<i class="foo" style="text-decoration: underline">f</i></p>',
            ],
            // Custom element with only style specified
            [
                '{"inlineStyles":{"RED":{"style":{"fontSize":"12","color":"red"}}}}',
                '{"entityMap":{},"blocks":[{"key":"99n0j","text":"asdf","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":3,"length":1,"style":"RED"}],"entityRanges":[]}]}',
                '<p>asd<span style="font-size: 12px; color: red">f</span></p>',
            ],
            // Custom element with all fields specified
            [
                '{"inlineStyles":{"FOO":{"element":"s","attributes":{"class":"foo"},"style":{"lineHeight":1}}}}',
                '{"entityMap":{},"blocks":[{"key":"99n0j","text":"asdf","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":3,"length":1,"style":"FOO"}],"entityRanges":[]}]}',
                '<p>asd<s class="foo" style="line-height: 1">f</s></p>',
            ],
            // Entity with a custom inline style
            [
                '{"inlineStyles":{"RED":{"style":{"color":"red"}}}}',
                '{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"RED"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}',
                '<p><a href="/"><span style="color: red">a</span></a></p>',
            ],
        ];
    }
}
