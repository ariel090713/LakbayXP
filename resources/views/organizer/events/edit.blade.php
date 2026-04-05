<x-layouts.app>
    <div class="max-w-3xl space-y-6">
        <flux:heading size="xl">Edit Event</flux:heading>

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

        <form method="POST" action="{{ route('organizer.events.update', $event) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Title</flux:label>
                    <flux:input name="title" value="{{ old('title', $event->title) }}" required />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input name="slug" value="{{ old('slug', $event->slug) }}" required />
                    <flux:error name="slug" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea name="description" rows="4">{{ old('description', $event->description) }}</flux:textarea>
                <flux:error name="description" />
            </flux:field>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Place</flux:label>
                    <flux:select name="place_id" required>
                        <flux:select.option value="">Select a place</flux:select.option>
                        @foreach($places as $place)
                            <flux:select.option value="{{ $place->id }}" :selected="old('place_id', $event->place_id) == $place->id">
                                {{ $place->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="place_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Category</flux:label>
                    <flux:select name="category" required>
                        <flux:select.option value="">Select category</flux:select.option>
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->value }}" :selected="old('category', $event->category->value) === $category->value">
                                {{ ucwords(str_replace('_', ' ', $category->value)) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>
            </div>

            <div class="grid gap-6 sm:grid-cols-3">
                <flux:field>
                    <flux:label>Event Date</flux:label>
                    <flux:input type="date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required />
                    <flux:error name="event_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fee (₱)</flux:label>
                    <flux:input type="number" name="fee" value="{{ old('fee', $event->fee) }}" step="0.01" min="0" />
                    <flux:error name="fee" />
                </flux:field>

                <flux:field>
                    <flux:label>Max Slots</flux:label>
                    <flux:input type="number" name="max_slots" value="{{ old('max_slots', $event->max_slots) }}" min="1" required />
                    <flux:error name="max_slots" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Meeting Place</flux:label>
                <flux:input name="meeting_place" value="{{ old('meeting_place', $event->meeting_place) }}" />
                <flux:error name="meeting_place" />
            </flux:field>

            <flux:field>
                <flux:label>Auto-Approve Bookings</flux:label>
                <flux:checkbox name="auto_approve_bookings" value="1" :checked="old('auto_approve_bookings', $event->auto_approve_bookings)" label="Automatically approve new bookings" />
                <flux:error name="auto_approve_bookings" />
            </flux:field>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">Update Event</flux:button>
                <flux:button href="{{ route('organizer.events.index') }}">Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
