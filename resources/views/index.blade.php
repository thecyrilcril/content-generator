<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="relative sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Home</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div
                x-data="{
                    article: '',
                    form: {
                        author_style: null,
                        prompt: null
                    },
                    validationErrors: {
                        author_style: null,
                        prompt: null
                    },
                    formState: {
                        processing: false
                    },
                    clearObjectProperties(obj) {
                      for (const [key, value] of Object.entries(obj)) {
                        const keyName = `${key}`
                        obj[keyName] = null
                      }
                    },
                    renderErrors(responseErrors) {
                        this.validationErrors = responseErrors
                    },
                    async generateArticle() {
                        try {
                            this.formState.processing = true
                            this.clearObjectProperties(this.validationErrors)
                            const response = await axios.post(`{{ route('generate-article') }}`, this.form)
                            this.article = response.data.article
                            this.formState.processing = false
                        } catch(error) {
                            this.formState.processing = false
                            if (error.response && error.response.status === 422) {
                                this.renderErrors(error.response.data.errors)
                            }
                            if (error.response && error.response.status === 400) {
                                this.validationErrors.prompt = error.response.data.errors
                            }

                        }
                    }
                }"
                class="max-w-5xl mx-auto p-6 lg:p-8">
                <form
                    @submit.prevent="generateArticle"
                    class="w-full dark:text-gray-100"
                >
                    <div class="mb-3">
                        <label for="author_style">Author Style</label>
                        <select x-model="form.author_style" name="author_style" id="author_style" class="dark:text-inherit dark:bg-gray-900">
                         <option selected>--Please select a tone--</option>
                        @foreach($author_styles as $key => $value)
                        <option value="{{ $value }}">{{ $key }}</option>
                        @endforeach
                        </select>
                        <span
                            x-show="validationErrors.author_style"
                            x-text="validationErrors.author_style ? validationErrors.author_style.toString() : ``"
                            class="dark:text-purple-300 tracking-wide"
                        >
                        </span>
                    </div>
                    <div class="mb-3" class="grid grid-rows-1 space-y-3">
                        <label for="prompt" class="w-full inline-flex mb-1">Prompt</label>
                        <textarea x-model="form.prompt" id="prompt" class="w-full p-4 rounded dark:bg-gray-900 ring-1 dark:ring-gray-400"></textarea>
                        <span
                            x-show="validationErrors.prompt"
                            x-text="validationErrors.prompt ? validationErrors.prompt.toString() : ``"
                            class="dark:text-purple-300 tracking-wide"
                        >
                        </span>

                    </div>
                    <div class="mb-3">
                        <button
                            type="submit"
                            ::class="{'cursor-not-allowed' : formState.processing }"
                            class="inline-flex items-center px-4 py-2 font-semibold leading-6 shadow rounded-md text-white bg-purple-500 hover:bg-purple-400 transition ease-in-out duration-150"
                            ::disabled="formState.processing"
                        >
                            <svg x-show="formState.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-show="formState.processing">Generating...</span>
                            <span x-show="!formState.processing">Generate</span>
                          </button>
                    </div>
                </form>
                <article x-html="article" class="dark:text-gray-300"></article>
            </div>
        </div>
    </body>
</html>
