@props(['model', 'type', 'title' => 'Photos', 'hint' => null])
{{--
    Photo manager for any model with HasPhotos. type = morph map key (classroom, activity, classroom_activity, camp, album).
    Drag to reorder, star the main photo, edit captions inline, delete, and drag-and-drop multi-upload.
--}}
@php $photos = $model->photos()->get(); @endphp
<section {{ $attributes->class(['card']) }} data-photo-manager
    data-store-url="{{ route('admin.content.photos.store') }}"
    data-update-url="{{ route('admin.content.photos.update', ['photo' => '__ID__']) }}"
    data-type="{{ $type }}" data-owner="{{ $model->getKey() }}">
    <div class="card-pad flex flex-wrap items-start justify-between gap-3 border-b border-line">
        <div class="min-w-0">
            <h2 class="card-title">{{ $title }}</h2>
            <p class="hint">{{ $hint ?? 'Drag the handle to reorder. Star a photo to make it the main photo.' }}</p>
        </div>
        <span class="badge badge-muted" data-photo-count>{{ $photos->count() }} {{ $photos->count() === 1 ? 'photo' : 'photos' }}</span>
    </div>

    <div class="card-pad space-y-4">
        <label data-dropzone
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-line bg-canvas/60 px-4 py-7 text-center transition-colors hover:border-brand/40 hover:bg-brand-soft/40 data-[over]:border-brand data-[over]:bg-brand-soft/60">
            <input type="file" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" data-photo-input>
            <span class="grid size-11 place-items-center rounded-2xl bg-white text-brand shadow-sm"><x-icon name="upload" /></span>
            <span class="font-bold text-ink">Drop photos here or click to choose</span>
            <span class="max-w-md text-xs text-muted">JPG, PNG or WebP, up to 10 MB each and 30 at a time. Landscape photos at least 1200 px wide look best — large files are resized automatically.</span>
        </label>

        <div data-progress hidden class="rounded-xl border border-line bg-white p-3">
            <div class="mb-1.5 flex justify-between gap-3 text-xs font-bold text-muted">
                <span data-progress-label>Uploading…</span><span data-progress-percent>0%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-canvas">
                <div data-progress-bar class="h-full rounded-full bg-brand transition-[width]" style="width: 0%"></div>
            </div>
        </div>

        <div data-photo-error hidden class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700"></div>

        <p data-photo-empty @if ($photos->isNotEmpty()) hidden @endif class="py-2 text-center text-sm text-muted">No photos yet. Upload a few so parents can see what happens here.</p>

        <ul data-photo-list data-sortable="{{ route('admin.content.photos.reorder') }}" class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
            @foreach ($photos as $photo)
                @include('admin.content.partials.photo-item', ['photo' => $photo])
            @endforeach
        </ul>
    </div>

    <template data-photo-template>
        @include('admin.content.partials.photo-item', ['photo' => null])
    </template>
</section>

@once
    @push('scripts')
        <script>
            (() => {
                const CHUNK = 4; // files per request, keeps each request under the server's post size limit

                const initManager = (root) => {
                    const list = root.querySelector('[data-photo-list]');
                    const input = root.querySelector('[data-photo-input]');
                    const zone = root.querySelector('[data-dropzone]');
                    const template = root.querySelector('template[data-photo-template]');
                    const progress = root.querySelector('[data-progress]');
                    const errorBox = root.querySelector('[data-photo-error]');
                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    const updateUrl = (id) => root.dataset.updateUrl.replace('__ID__', id);

                    const refresh = () => {
                        const n = list.querySelectorAll(':scope > [data-id]').length;
                        root.querySelector('[data-photo-count]').textContent = n + (n === 1 ? ' photo' : ' photos');
                        root.querySelector('[data-photo-empty]').hidden = n > 0;
                    };
                    const showError = (message) => { errorBox.textContent = message; errorBox.hidden = !message; };
                    const setFeatured = (li, on) => {
                        li.querySelector('[data-feature]').setAttribute('aria-pressed', on ? 'true' : 'false');
                        li.querySelector('[data-featured-badge]').hidden = !on;
                    };

                    const build = (photo) => {
                        const li = template.content.firstElementChild.cloneNode(true);
                        li.dataset.id = photo.id;
                        li.querySelector('[data-photo-img]').src = photo.url;
                        li.querySelector('[data-photo-img]').alt = photo.alt || '';
                        li.querySelector('[data-caption]').value = photo.caption || '';
                        setFeatured(li, photo.is_featured);
                        return li;
                    };

                    const send = (formData, onProgress) => new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', root.dataset.storeUrl);
                        xhr.setRequestHeader('X-CSRF-TOKEN', token);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.upload.onprogress = (e) => e.lengthComputable && onProgress(e.loaded / e.total);
                        xhr.onload = () => {
                            let data = null;
                            try { data = JSON.parse(xhr.responseText); } catch (e) {}
                            if (xhr.status >= 200 && xhr.status < 300 && data) return resolve(data);
                            if (xhr.status === 413) return reject(new Error('These photos are too large for the server. Try fewer or smaller photos.'));
                            const first = data && data.errors ? Object.values(data.errors)[0][0] : null;
                            reject(new Error(first || (data && data.message) || 'Upload failed. Please try again.'));
                        };
                        xhr.onerror = () => reject(new Error('Upload failed. Check your connection and try again.'));
                        xhr.send(formData);
                    });

                    const upload = async (fileList) => {
                        showError('');
                        let files = [...fileList].filter((f) => /^image\/(jpeg|png|webp)$/.test(f.type));
                        const skipped = fileList.length - files.length;
                        const tooBig = files.filter((f) => f.size > 10 * 1024 * 1024);
                        files = files.filter((f) => f.size <= 10 * 1024 * 1024);
                        const notes = [];
                        if (skipped) notes.push(skipped + ' file(s) skipped — only JPG, PNG or WebP photos can be uploaded.');
                        if (tooBig.length) notes.push(tooBig.length + ' photo(s) skipped — larger than 10 MB.');
                        if (files.length > 30) { notes.push('Only the first 30 photos were uploaded.'); files = files.slice(0, 30); }
                        if (!files.length) return showError(notes.join(' ') || 'Choose some photos to upload.');

                        const total = files.reduce((s, f) => s + f.size, 0) || 1;
                        let done = 0, uploaded = 0;
                        const bar = root.querySelector('[data-progress-bar]');
                        const setProgress = (ratio) => {
                            const pct = Math.min(100, Math.round(ratio * 100));
                            bar.style.width = pct + '%';
                            root.querySelector('[data-progress-percent]').textContent = pct + '%';
                        };
                        progress.hidden = false;
                        setProgress(0);

                        for (let i = 0; i < files.length; i += CHUNK) {
                            const chunk = files.slice(i, i + CHUNK);
                            const size = chunk.reduce((s, f) => s + f.size, 0);
                            root.querySelector('[data-progress-label]').textContent = `Uploading ${Math.min(i + CHUNK, files.length)} of ${files.length}…`;
                            const fd = new FormData();
                            fd.append('photoable_type', root.dataset.type);
                            fd.append('photoable_id', root.dataset.owner);
                            chunk.forEach((f) => fd.append('files[]', f));
                            try {
                                const data = await send(fd, (r) => setProgress((done + r * size) / total));
                                data.photos.forEach((photo) => list.appendChild(build(photo)));
                                uploaded += data.photos.length;
                                refresh();
                            } catch (e) {
                                notes.push(e.message);
                            }
                            done += size;
                            setProgress(done / total);
                        }

                        root.querySelector('[data-progress-label]').textContent = uploaded + (uploaded === 1 ? ' photo uploaded' : ' photos uploaded');
                        setTimeout(() => { progress.hidden = true; }, 2000);
                        showError(notes.join(' '));
                    };

                    input.addEventListener('change', () => { upload(input.files); input.value = ''; });
                    ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.dataset.over = ''; }));
                    ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); delete zone.dataset.over; }));
                    zone.addEventListener('drop', (e) => e.dataTransfer && upload(e.dataTransfer.files));

                    list.addEventListener('click', async (e) => {
                        const li = e.target.closest('li[data-id]');
                        if (!li) return;

                        if (e.target.closest('[data-delete]')) {
                            if (!confirm('Delete this photo? This can’t be undone.')) return;
                            const res = await window.csrfFetch(updateUrl(li.dataset.id), { method: 'DELETE' });
                            if (res.ok) { li.remove(); refresh(); } else { showError('Could not delete the photo. Please refresh and try again.'); }
                        }

                        if (e.target.closest('[data-feature]')) {
                            const on = li.querySelector('[data-feature]').getAttribute('aria-pressed') !== 'true';
                            const res = await window.csrfFetch(updateUrl(li.dataset.id), { method: 'PATCH', body: JSON.stringify({ is_featured: on }) });
                            if (!res.ok) return showError('Could not update the main photo. Please try again.');
                            list.querySelectorAll(':scope > li[data-id]').forEach((other) => setFeatured(other, on && other === li));
                        }
                    });

                    const timers = {};
                    const saveCaption = async (field) => {
                        const li = field.closest('li[data-id]');
                        const res = await window.csrfFetch(updateUrl(li.dataset.id), { method: 'PATCH', body: JSON.stringify({ caption: field.value }) });
                        field.classList.add(res.ok ? 'bg-lime/10' : 'bg-red-50');
                        setTimeout(() => field.classList.remove('bg-lime/10', 'bg-red-50'), 900);
                    };
                    list.addEventListener('input', (e) => {
                        const field = e.target.closest('[data-caption]');
                        if (!field) return;
                        const id = field.closest('li[data-id]').dataset.id;
                        clearTimeout(timers[id]);
                        timers[id] = setTimeout(() => saveCaption(field), 700);
                    });
                    list.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && e.target.matches('[data-caption]')) { e.preventDefault(); e.target.blur(); }
                    });
                };

                const boot = () => document.querySelectorAll('[data-photo-manager]').forEach(initManager);
                document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
            })();
        </script>
    @endpush
@endonce
