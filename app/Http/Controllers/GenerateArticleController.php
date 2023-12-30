<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use JustSteveKing\StatusCode\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\ChatGPT\OpenAiService;
use App\Http\Requests\GenerateArticleRequest;

class GenerateArticleController
{
    public function __invoke(GenerateArticleRequest $request, OpenAiService $openAiService)
    {

        try {
            $openAiService
                ->generateArticle(
                    prompt: $request->validated()['prompt'],
                    author_style: $request->validated()['author_style'],
                )
                ->generateImagePrompts(Cache::get(\App\Enums\CacheStore::Article->value))
                ->generateImages(Cache::get(\App\Enums\CacheStore::ImagePrompts->value));

            return new JsonResponse([
                'article' => $this->render()
            ], Http::OK());

        } catch(\OpenAI\Exceptions\ErrorException $exception) {
            return new JsonResponse([
                'errors' => $exception->getMessage()
            ], Http::BAD_REQUEST());
        }

    }

    private function render(): string
    {

        $ImageUrls = Cache::get(\App\Enums\CacheStore::Images->value);
        $generatedArticle = Cache::get(\App\Enums\CacheStore::Article->value);
        // initialize an article title
        $articleTitle = '';

        if ($this->articleHasTitle($generatedArticle)) {

            $articleArray = $this->getTitleAndBody($generatedArticle);

            // make an array of paragraphs
            $articleParagraphsArray = explode(".\n", $articleArray['body']);
            $articleTitle = $articleArray['title'];

            // remove extra newline character (\n)
            $paragraphs = array_map(fn($chunk) => Str::remove("\n", $chunk), $articleParagraphsArray);
        }

        if (! $this->articleHasTitle($generatedArticle) ) {
            // make an array of paragraphs
            $articleParagraphsArray = explode(".\n", $generatedArticle);

            // remove extra newline character (\n)
            $paragraphs = array_map(fn($chunk) => Str::remove("\n", $chunk), $articleParagraphsArray);

        }

        // 1. make the $paragraphs array into a collection
        // 2. wrap each paragraph in p tag with tailwind classes
        // 3. add the first image at the beginning of the article
        $paragraphsWithFeaturedImage = collect($paragraphs)
          ->map(function($paragraph) {
            if (Str::of($paragraph)->isMatch('/^(\w.+:)/')) {
                $subTitle = Str::of($paragraph)->match('/^(\w.+:)/')->value;
                $paragraphBody = Str::replace($subTitle, "", $paragraph);
                return
                  <<<TXT
                  <div class="mb-3">
                  <h4 class="mb-3 text-xl font-semibold">$subTitle</h4>
                  <p>$paragraphBody</p>
                  </div>
                  TXT;
            }
            return '<p class="mb-3">' . $paragraph . '</p>';
          })
          ->prepend('<img src="' . $ImageUrls[0] . '" alt="actor" class="inline-grid py-2 rounded" />')
          ->tap(function ($collection) use($articleTitle) {
              (!empty($articleTitle)) ?
                $collection->prepend('<h3 class="mb-3 font-semibold text-3xl">' . $articleTitle . '</h3>') :
                $collection;
          });

        // 1. split the $paragraphsWithFeaturedImage into 2
        // by generating a random index between 3 and (size of the collection) - 2
        // 2. add image 2 to the beginning of these chunk
        $chunkTwo = $paragraphsWithFeaturedImage->splice(rand(3, $paragraphsWithFeaturedImage->count()-2))
        ->prepend('<img src="' . $ImageUrls[1] . '" alt="effect" class="inline-grid py-2 rounded" />');

        // merge the chucks and make into string
        return $paragraphsWithFeaturedImage->merge($chunkTwo)->join('');

    }

    private function articleHasTitle(string $article): bool
    {
       return Str::of($article)->isMatch('/^(Title:.+\n)/');
    }

    private function getTitleAndBody(string $article): array
    {
       $title = Str::of($article)->match('/^(Title:.+\n)/')->value;
       $body = Str::replace($title, "", $article);
       $title = Str::remove("\n", $title);

       return [
            'title' => $title,
            'body' => $body,
       ];

    }


}
