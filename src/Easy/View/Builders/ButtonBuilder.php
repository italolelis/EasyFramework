<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\View\Builders;

class ButtonBuilder
{

    public static function submitButton($name, $buttonText, array $htmlAttributes = array())
    {
        $buttonTag = new TagBuilder("input");

        $buttonTag->mergeAttribute("type", "submit");

        if (!empty($name)) {
            $buttonTag->mergeAttribute("name", $name);
        }

        if (!empty($buttonText)) {
            $buttonTag->mergeAttribute("value", $buttonText);
        }

        $buttonTag->mergeAttributes($htmlAttributes);
        return $buttonTag->toString(TagRenderMode::SELF_CLOSING);
    }

    public static function submitImage($name, $sourceUrl, array $htmlAttributes = array())
    {
        $buttonTag = new TagBuilder("input");

        $buttonTag->mergeAttribute("type", "image");

        if (!empty($name)) {
            $buttonTag->mergeAttribute("name", $name);
        }

        if (!empty($sourceUrl)) {
            $buttonTag->mergeAttribute("src", $sourceUrl);
        }

        $buttonTag->mergeAttributes($htmlAttributes);
        return $buttonTag->toString(TagRenderMode::SELF_CLOSING);
    }

    public static function button($name, $buttonText, $type = HtmlButtonType::SUBMIT, $onClickMethod = null, $htmlAttributes = array())
    {
        $buttonTag = new TagBuilder("button");

        if (!empty($name)) {
            $buttonTag->mergeAttribute("name", $name);
        }

        if (!empty($onClickMethod)) {
            $buttonTag->mergeAttribute("onclick", $onClickMethod);
        }

        $buttonTag->mergeAttribute("type", $type);
        $buttonTag->setInnerHtml($buttonText);
        $buttonTag->mergeAttributes($htmlAttributes);

        return $buttonTag->toString(TagRenderMode::NORMAL);
    }

}