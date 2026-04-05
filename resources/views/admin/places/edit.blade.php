<x-layouts.app>
    <div class="max-w-3xl space-y-6">
        <flux:heading size="xl">Edit Place: {{ $place->name }}</flux:heading>

        @if($errors->any())
            <flux:callout variant="danger">
                <p>Please fix the following errors:</p>
                <ul class="mt-1 list-inside list-disc text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('admin.places.update', $place) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input name="name" value="{{ old('name', $place->name) }}" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input name="slug" value="{{ old('slug', $place->slug) }}" required />
                    <flux:error name="slug" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea name="description" rows="4">{{ old('description', $place->description) }}</flux:textarea>
                <flux:error name="description" />
            </flux:field>

            <div class="grid gap-6 sm:grid-cols-3">
                <flux:field>
                    <flux:label>Category</flux:label>
                    <flux:select name="category" required>
                        <flux:select.option value="">Select category</flux:select.option>
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->value }}" :selected="old('category', $place->category->value) === $category->value">
                                {{ ucwords(str_replace('_', ' ', $category->value)) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>

                <flux:field>
                    <flux:label>Region</flux:label>
                    <flux:input name="region" value="{{ old('region', $place->region) }}" />
                    <flux:error name="region" />
                </flux:field>

                <flux:field>
                    <flux:label>Province</flux:label>
                    <flux:input name="province" value="{{ old('province', $place->province) }}" />
                    <flux:error name="province" />
                </flux:field>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Latitude</flux:label>
                    <flux:input type="number" name="latitude" value="{{ old('latitude', $place->latitude) }}" step="0.0000001" min="-90" max="90" />
                    <flux:error name="latitude" />
                </flux:field>

                <flux:field>
                    <flux:label>Longitude</flux:label>
                    <flux:input type="number" name="longitude" value="{{ old('longitude', $place->longitude) }}" step="0.0000001" min="-180" max="180" />
                    <flux:error name="longitude" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Cover Image</flux:label>
                @if($place->cover_image_path)
                    <div class="mb-2">
                        <img src="{{ Storage::disk('s3')->url($place->cover_image_path) }}" alt="Current cover image" class="h-32 w-auto rounded-lg object-cover" />
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Current cover image</p>
                    </div>
                @endif
                <flux:input type="file" name="cover_image" accept="image/*" />
                <flux:error name="cover_image" />
            </flux:field>

            <flux:separator />

            <div class="space-y-4">
                <flux:heading size="lg">Category-Specific Fields</flux:heading>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Optional fields based on the place category.</p>

                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Difficulty</flux:label>
                        <flux:select name="category_fields[difficulty]">
                            <flux:select.option value="">Select difficulty</flux:select.option>
                            @php $currentDifficulty = old('category_fields.difficulty', $place->category_fields['difficulty'] ?? ''); @endphp
                            <flux:select.option value="easy" :selected="$currentDifficulty === 'easy'">Easy</flux:select.option>
                            <flux:select.option value="moderate" :selected="$currentDifficulty === 'moderate'">Moderate</flux:select.option>
                            <flux:select.option value="hard" :selected="$currentDifficulty === 'hard'">Hard</flux:select.option>
                            <flux:select.option value="extreme" :selected="$currentDifficulty === 'extreme'">Extreme</flux:select.option>
                        </flux:select>
                        <flux:error name="category_fields.difficulty" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Meters Above Sea Level</flux:label>
                        <flux:input type="number" name="category_fields[meters_above_sea_level]" value="{{ old('category_fields.meters_above_sea_level', $place->category_fields['meters_above_sea_level'] ?? '') }}" />
                        <flux:error name="category_fields.meters_above_sea_level" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Trail Class</flux:label>
                        <flux:input type="number" name="category_fields[trail_class]" value="{{ old('category_fields.trail_class', $place->category_fields['trail_class'] ?? '') }}" min="1" max="9" />
                        <flux:error name="category_fields.trail_class" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Estimated Hours</flux:label>
                        <flux:input type="number" name="category_fields[estimated_hours]" value="{{ old('category_fields.estimated_hours', $place->category_fields['estimated_hours'] ?? '') }}" step="0.5" />
                        <flux:error name="category_fields.estimated_hours" />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">Update Place</flux:button>
                <flux:button href="{{ route('admin.places.index') }}">Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
