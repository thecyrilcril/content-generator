<?php

declare(strict_types=1);

namespace App\Services\ChatGPT;

use Illuminate\Support\Str;
use Illuminate\Http\Client\Pool;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use OpenAI\Responses\Chat\CreateResponse;
use Illuminate\Http\Client\RequestException;

class OpenAiService
{
    public function generateArticle(string $prompt, string $author_style): self
    {
        $this->clearArticleFromCache();

        $response = OpenAI::chat()->create([
            'model' => config('openai.chat_model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "When I ask for help to write something, you will reply with $author_style tone"
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
        ]);
        $this->cacheArticle($this->getContent($response));

        return $this;
    }

    public function generateImagePrompts(string $text, int $summary_sentence_count = 2): self
    {
        $this->clearImagePromptsFromCache();

        $response = OpenAI::chat()->create([
            'model' => config('openai.chat_model'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "write in $summary_sentence_count simple and descriptive sentences(not more than 7 words) the actors and effect from the following article: $text in the format: actors:..., effects:...on what or who"
                ],
            ],
        ]);

        $sentences = explode("\n", $this->getContent($response));

        $this->cacheImagePrompts(
            array_map(
                fn($sentence) => Str::of($sentence)->match('/([^Actors: | ^Effects: ][\w].+[^.])/')->value,
                $sentences
            )
        );

        return $this;
    }

    public function generateImages(array $prompt_sentences )
    {

        $this->clearImagesFromCache();

        $responses = Http::pool(fn (Pool $pool) => [
            $pool->openai()->post('/images/generations', [
              "model" => config('openai.image_model'),
              "prompt" => $prompt_sentences[0],
              "n" => 1,
              "size" => "512x512"
            ]),

            $pool->openai()->post('/images/generations', [
              "model" => config('openai.image_model'),
              "prompt" => $prompt_sentences[1],
              "n" => 1,
              "size" => "512x512"
            ]),
        ]);

        $images = [];

        foreach($responses as $response) {
            $images[] = $response->throw(
                fn() => throw new \OpenAI\Exceptions\ErrorException($response->json()['error'])
            )->object()->data[0]->url;
        }

        $this->cacheImages($images);

        return $this;
    }

    private function getContent(CreateResponse $response): string
    {
        return $response->choices[0]->message->content;
    }

    private function cacheArticle(string $article): string
    {
        return Cache::rememberForever(
            \App\Enums\CacheStore::Article->value,
            fn () => Str::replace('"', '\'', $article)
        );
    }

    private function cacheImagePrompts(array $image_prompts): void
    {
        Cache::rememberForever(
            \App\Enums\CacheStore::ImagePrompts->value,
            fn () => $image_prompts
        );
    }
    private function cacheImages(array $image_urls): void
    {
        Cache::rememberForever(
            \App\Enums\CacheStore::Images->value,
            fn () => $image_urls
        );
    }

    private function clearArticleFromCache()
    {
        Cache::forget(\App\Enums\CacheStore::Article->value);
    }

    private function clearImagePromptsFromCache()
    {
        Cache::forget(\App\Enums\CacheStore::ImagePrompts->value);
    }

    private function clearImagesFromCache()
    {
        Cache::forget(\App\Enums\CacheStore::Images->value);
    }
}
