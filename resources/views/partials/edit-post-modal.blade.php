<!-- ==========================================
     EDIT TIMELINE POST MODAL
     ========================================== -->
<div id="editTimelinePostModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-2xl w-full p-4 sm:p-6 shadow-2xl border border-stone-200 dark:border-stone-800 relative max-h-[90vh] overflow-y-auto my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-stone-200 dark:border-stone-800">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-stone-100">Edit Story</h3>
            </div>
            <button type="button" onclick="closeEditTimelinePostModal()" class="p-1.5 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="editTimelinePostForm" onsubmit="submitEditTimelinePost(event)" class="mt-4 space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="editTimelinePostId" name="post_id">

            <!-- Content -->
            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                    Story Content *
                </label>
                <textarea 
                    id="editTimelinePostContent" 
                    name="content" 
                    required 
                    rows="4" 
                    class="w-full text-sm sm:text-base border-2 border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-850 text-stone-800 dark:text-stone-100 rounded-2xl p-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none leading-relaxed transition"></textarea>
            </div>

            <!-- Chapter & Language -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                        Life Chapter
                    </label>
                    <select 
                        id="editTimelinePostChapter" 
                        name="chapter" 
                        class="w-full text-xs sm:text-sm border-2 border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-800 dark:text-stone-200 rounded-xl p-2.5 focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="">None / General</option>
                        <option value="Cultural Tech Innovation">Cultural Tech Innovation</option>
                        <option value="Software Architecture Mastery">Software Architecture Mastery</option>
                        <option value="University & Cultural Awakening">University & Cultural Awakening</option>
                        <option value="Roots & Foundation">Roots & Foundation</option>
                        <option value="Community Building">Community Building</option>
                        <option value="Heritage Preservation">Heritage Preservation</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                        Story Language
                    </label>
                    <select 
                        id="editTimelinePostLanguage" 
                        name="language" 
                        class="w-full text-xs sm:text-sm border-2 border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-800 dark:text-stone-200 rounded-xl p-2.5 focus:ring-2 focus:ring-amber-500 outline-none">
                        @foreach(['English', 'Hausa', 'Yoruba', 'Igbo', 'Swahili', 'Nigerian Pidgin', 'French', 'Spanish', 'Arabic', 'Zulu', 'Amharic', 'Fulani', 'Twi', 'Edo'] as $lang)
                            <option value="{{ $lang }}">{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                    Location
                </label>
                <div class="relative">
                    <i data-lucide="map-pin" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                    <input 
                        type="text" 
                        id="editTimelinePostLocation" 
                        name="location" 
                        placeholder="e.g. Lagos, Nigeria"
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border-2 border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-800 dark:text-stone-100 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>

            <!-- Privacy -->
            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                    Visibility
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-stone-200 dark:border-stone-700 cursor-pointer hover:border-amber-500 text-xs font-medium text-stone-800 dark:text-stone-200">
                        <input type="radio" name="privacy" value="public" id="editTimelinePrivacyPublic" class="text-amber-500">
                        <span>Public</span>
                    </label>
                    <label class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-stone-200 dark:border-stone-700 cursor-pointer hover:border-amber-500 text-xs font-medium text-stone-800 dark:text-stone-200">
                        <input type="radio" name="privacy" value="private" id="editTimelinePrivacyPrivate" class="text-amber-500">
                        <span>Private</span>
                    </label>
                    <label class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl border border-stone-200 dark:border-stone-700 cursor-pointer hover:border-amber-500 text-xs font-medium text-stone-800 dark:text-stone-200">
                        <input type="radio" name="privacy" value="vault" id="editTimelinePrivacyVault" class="text-amber-500">
                        <span>Vault</span>
                    </label>
                </div>
            </div>

            <!-- Existing Media Preview & Removal -->
            <div id="editTimelineMediaContainer" class="hidden p-3 rounded-2xl bg-stone-100 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-stone-700 dark:text-stone-300">Attached Media</span>
                    <label class="inline-flex items-center gap-1.5 text-rose-600 dark:text-rose-400 font-semibold cursor-pointer">
                        <input type="checkbox" name="remove_image" id="editTimelineRemoveImage" value="1" class="rounded text-rose-600">
                        <span>Remove Media</span>
                    </label>
                </div>
                <div id="editTimelineMediaPreview" class="rounded-xl overflow-hidden max-h-48 flex items-center justify-center bg-black/10"></div>
            </div>

            <!-- New Media Upload (Optional Replacement) -->
            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                    Replace or Attach Media
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="p-2 border border-dashed border-stone-300 dark:border-stone-700 hover:border-amber-500 rounded-xl text-center cursor-pointer text-xs font-medium text-stone-600 dark:text-stone-400 flex flex-col items-center gap-1">
                        <i data-lucide="image" class="w-4 h-4 text-amber-500"></i>
                        <span>Photo</span>
                        <input type="file" name="image" accept="image/*" class="hidden" onchange="previewEditTimelineMedia(this, 'image')">
                    </label>
                    <label class="p-2 border border-dashed border-stone-300 dark:border-stone-700 hover:border-amber-500 rounded-xl text-center cursor-pointer text-xs font-medium text-stone-600 dark:text-stone-400 flex flex-col items-center gap-1">
                        <i data-lucide="video" class="w-4 h-4 text-blue-500"></i>
                        <span>Video</span>
                        <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" class="hidden" onchange="previewEditTimelineMedia(this, 'video')">
                    </label>
                    <label class="p-2 border border-dashed border-stone-300 dark:border-stone-700 hover:border-amber-500 rounded-xl text-center cursor-pointer text-xs font-medium text-stone-600 dark:text-stone-400 flex flex-col items-center gap-1">
                        <i data-lucide="mic" class="w-4 h-4 text-purple-500"></i>
                        <span>Audio</span>
                        <input type="file" name="audio" accept="audio/mp3,audio/wav,audio/ogg,audio/m4a" class="hidden" onchange="previewEditTimelineMedia(this, 'audio')">
                    </label>
                </div>
                <div id="editTimelineNewMediaNotice" class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-medium hidden"></div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-3 border-t border-stone-200 dark:border-stone-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditTimelinePostModal()" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    Cancel
                </button>
                <button type="submit" id="editTimelineSubmitBtn" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md transition active:scale-95 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     EDIT COMMUNITY POST MODAL
     ========================================== -->
<div id="editCommunityPostModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-xl w-full p-4 sm:p-6 shadow-2xl border border-stone-200 dark:border-stone-800 relative max-h-[90vh] overflow-y-auto my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-stone-200 dark:border-stone-800">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-stone-100">Edit Community Post</h3>
            </div>
            <button type="button" onclick="closeEditCommunityPostModal()" class="p-1.5 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="editCommunityPostForm" onsubmit="submitEditCommunityPost(event)" class="mt-4 space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="editCommunityPostId" name="post_id">
            <input type="hidden" id="editCommunityId" name="community_id">

            <!-- Content -->
            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                    Post Message *
                </label>
                <textarea 
                    id="editCommunityPostContent" 
                    name="content" 
                    required 
                    rows="4" 
                    class="w-full text-sm sm:text-base border-2 border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-850 text-stone-800 dark:text-stone-100 rounded-2xl p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none leading-relaxed transition"></textarea>
            </div>

            <!-- Existing Media Preview & Removal -->
            <div id="editCommunityMediaContainer" class="hidden p-3 rounded-2xl bg-stone-100 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-stone-700 dark:text-stone-300">Attached Media</span>
                    <label class="inline-flex items-center gap-1.5 text-rose-600 dark:text-rose-400 font-semibold cursor-pointer">
                        <input type="checkbox" name="remove_image" id="editCommunityRemoveImage" value="1" class="rounded text-rose-600">
                        <span>Remove Media</span>
                    </label>
                </div>
                <div id="editCommunityMediaPreview" class="rounded-xl overflow-hidden max-h-48 flex items-center justify-center bg-black/10"></div>
            </div>

            <!-- New Media Upload -->
            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                    Replace or Attach Media
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="p-2 border border-dashed border-stone-300 dark:border-stone-700 hover:border-orange-500 rounded-xl text-center cursor-pointer text-xs font-medium text-stone-600 dark:text-stone-400 flex flex-col items-center gap-1">
                        <i data-lucide="image" class="w-4 h-4 text-orange-500"></i>
                        <span>Photo</span>
                        <input type="file" name="image" accept="image/*" class="hidden" onchange="previewEditCommunityMedia(this, 'image')">
                    </label>
                    <label class="p-2 border border-dashed border-stone-300 dark:border-stone-700 hover:border-orange-500 rounded-xl text-center cursor-pointer text-xs font-medium text-stone-600 dark:text-stone-400 flex flex-col items-center gap-1">
                        <i data-lucide="video" class="w-4 h-4 text-blue-500"></i>
                        <span>Video</span>
                        <input type="file" name="video" accept="video/mp4,video/webm" class="hidden" onchange="previewEditCommunityMedia(this, 'video')">
                    </label>
                    <label class="p-2 border border-dashed border-stone-300 dark:border-stone-700 hover:border-orange-500 rounded-xl text-center cursor-pointer text-xs font-medium text-stone-600 dark:text-stone-400 flex flex-col items-center gap-1">
                        <i data-lucide="mic" class="w-4 h-4 text-purple-500"></i>
                        <span>Audio</span>
                        <input type="file" name="audio" accept="audio/mp3,audio/wav,audio/ogg" class="hidden" onchange="previewEditCommunityMedia(this, 'audio')">
                    </label>
                </div>
                <div id="editCommunityNewMediaNotice" class="text-xs text-orange-600 dark:text-orange-400 mt-1 font-medium hidden"></div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-3 border-t border-stone-200 dark:border-stone-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditCommunityPostModal()" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    Cancel
                </button>
                <button type="submit" id="editCommunitySubmitBtn" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-md transition active:scale-95 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     GLOBAL DELETE POST CONFIRMATION MODAL
     ========================================== -->
<div id="deletePostConfirmationModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-stone-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 dark:border-stone-800 relative text-center animate-in fade-in zoom-in-95 duration-150">
        <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-4 border border-red-200 dark:border-red-900/50 shadow-inner">
            <i data-lucide="trash-2" class="w-7 h-7"></i>
        </div>
        <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100" id="deletePostModalTitle">Delete Post?</h3>
        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 mt-2 leading-relaxed" id="deletePostModalMessage">
            Are you sure you want to delete this story? This will permanently remove it along with all interactions, taps, and responses. This action cannot be undone.
        </p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <button type="button" onclick="closeDeletePostModal()" class="w-full py-2.5 rounded-xl text-xs sm:text-sm font-bold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 border border-stone-200 dark:border-stone-700 transition">
                Keep Story
            </button>
            <button type="button" id="deletePostConfirmBtn" onclick="executePostDeletion()" class="w-full py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-md shadow-red-600/30 transition active:scale-95 flex items-center justify-center gap-1.5">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span id="deletePostConfirmBtnText">Yes, Delete</span>
            </button>
        </div>
    </div>
</div>

<script>
    // State for deletion
    let pendingDeletionConfig = null;

    // --- TIMELINE POST EDIT LOGIC ---
    function openEditTimelinePostModal(postId) {
        const modal = document.getElementById('editTimelinePostModal');
        if (!modal) return;

        // Reset fields
        document.getElementById('editTimelinePostId').value = postId;
        document.getElementById('editTimelinePostContent').value = 'Loading story...';
        document.getElementById('editTimelineMediaContainer').classList.add('hidden');
        document.getElementById('editTimelineMediaPreview').innerHTML = '';
        document.getElementById('editTimelineNewMediaNotice').classList.add('hidden');
        document.getElementById('editTimelineNewMediaNotice').textContent = '';
        if (document.getElementById('editTimelineRemoveImage')) {
            document.getElementById('editTimelineRemoveImage').checked = false;
        }

        modal.classList.remove('hidden');

        fetch(`/timeline/${postId}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to load story details.');
            return res.json();
        })
        .then(data => {
            if (data.success && data.post) {
                const p = data.post;
                document.getElementById('editTimelinePostContent').value = p.content || '';
                if (document.getElementById('editTimelinePostChapter')) {
                    document.getElementById('editTimelinePostChapter').value = p.chapter || '';
                }
                if (document.getElementById('editTimelinePostLocation')) {
                    document.getElementById('editTimelinePostLocation').value = p.location || '';
                }
                if (document.getElementById('editTimelinePostLanguage') && p.language) {
                    document.getElementById('editTimelinePostLanguage').value = p.language;
                }

                // Privacy radio
                const privacy = p.privacy || 'public';
                const rad = document.querySelector(`input[name="privacy"][value="${privacy}"]`);
                if (rad) rad.checked = true;

                // Existing Media
                const mediaContainer = document.getElementById('editTimelineMediaContainer');
                const mediaPreview = document.getElementById('editTimelineMediaPreview');
                if (p.image_url) {
                    mediaContainer.classList.remove('hidden');
                    mediaPreview.innerHTML = `<img src="${p.image_url}" class="max-h-44 object-contain rounded-lg" alt="Current image">`;
                } else if (p.video_url) {
                    mediaContainer.classList.remove('hidden');
                    mediaPreview.innerHTML = `<video src="${p.video_url}" controls class="max-h-44 w-full rounded-lg"></video>`;
                } else if (p.audio_url) {
                    mediaContainer.classList.remove('hidden');
                    mediaPreview.innerHTML = `<audio src="${p.audio_url}" controls class="w-full"></audio>`;
                }
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch(err => {
            console.error('Error fetching post for edit:', err);
            // Fallback to reading DOM content if network fails
            const domPost = document.getElementById(`post-original-content-${postId}`) || document.getElementById(`post-${postId}`);
            if (domPost) {
                document.getElementById('editTimelinePostContent').value = domPost.innerText.trim();
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeEditTimelinePostModal() {
        const modal = document.getElementById('editTimelinePostModal');
        if (modal) modal.classList.add('hidden');
    }

    function previewEditTimelineMedia(input, type) {
        const notice = document.getElementById('editTimelineNewMediaNotice');
        if (input.files && input.files[0]) {
            notice.classList.remove('hidden');
            notice.textContent = `Selected new ${type}: ${input.files[0].name} (will replace existing media upon saving)`;
        }
    }

    function submitEditTimelinePost(event) {
        event.preventDefault();
        const form = document.getElementById('editTimelinePostForm');
        const postId = document.getElementById('editTimelinePostId').value;
        const submitBtn = document.getElementById('editTimelineSubmitBtn');
        const origBtnText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i><span>Saving...</span>';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        const formData = new FormData(form);

        fetch(`/timeline/${postId}`, {
            method: 'POST', // Blade spoofed PUT
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to update story.');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                // Update post content in DOM if element exists
                const origContent = document.getElementById(`post-original-content-${postId}`);
                if (origContent) {
                    origContent.innerHTML = data.post.formatted_content || data.post.content;
                }
                const translatedContainer = document.getElementById(`post-translated-content-${postId}`);
                if (translatedContainer) {
                    translatedContainer.classList.add('hidden');
                }
                const translateBtnText = document.getElementById(`btn-translate-text-${postId}`);
                if (translateBtnText) {
                    translateBtnText.innerText = 'See Translation';
                }

                closeEditTimelinePostModal();
                alert(data.message || 'Story updated successfully!');
                
                // If page had media changes, reload or update media card
                if (formData.get('image') || formData.get('video') || formData.get('audio') || formData.get('remove_image')) {
                    window.location.reload();
                }
            } else {
                alert(data.error || 'Failed to update story.');
            }
        })
        .catch(err => {
            console.error('Update story error:', err);
            // Fallback: standard form submit
            form.submit();
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnText;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    // --- COMMUNITY POST EDIT LOGIC ---
    function openEditCommunityPostModal(communityId, postId) {
        const modal = document.getElementById('editCommunityPostModal');
        if (!modal) return;

        document.getElementById('editCommunityId').value = communityId;
        document.getElementById('editCommunityPostId').value = postId;
        document.getElementById('editCommunityPostContent').value = 'Loading post...';
        document.getElementById('editCommunityMediaContainer').classList.add('hidden');
        document.getElementById('editCommunityMediaPreview').innerHTML = '';
        document.getElementById('editCommunityNewMediaNotice').classList.add('hidden');
        document.getElementById('editCommunityNewMediaNotice').textContent = '';
        if (document.getElementById('editCommunityRemoveImage')) {
            document.getElementById('editCommunityRemoveImage').checked = false;
        }

        modal.classList.remove('hidden');

        fetch(`/communities/${communityId}/posts/${postId}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to load community post.');
            return res.json();
        })
        .then(data => {
            if (data.success && data.post) {
                const p = data.post;
                document.getElementById('editCommunityPostContent').value = p.content || '';

                const mediaContainer = document.getElementById('editCommunityMediaContainer');
                const mediaPreview = document.getElementById('editCommunityMediaPreview');
                if (p.image_url) {
                    mediaContainer.classList.remove('hidden');
                    mediaPreview.innerHTML = `<img src="${p.image_url}" class="max-h-44 object-contain rounded-lg" alt="Current image">`;
                } else if (p.video_url) {
                    mediaContainer.classList.remove('hidden');
                    mediaPreview.innerHTML = `<video src="${p.video_url}" controls class="max-h-44 w-full rounded-lg"></video>`;
                } else if (p.audio_url) {
                    mediaContainer.classList.remove('hidden');
                    mediaPreview.innerHTML = `<audio src="${p.audio_url}" controls class="w-full"></audio>`;
                }
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch(err => {
            console.error('Error fetching community post for edit:', err);
            const domPost = document.getElementById(`post-${postId}`);
            if (domPost) {
                const pTag = domPost.querySelector('p');
                if (pTag) document.getElementById('editCommunityPostContent').value = pTag.innerText.trim();
            }
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeEditCommunityPostModal() {
        const modal = document.getElementById('editCommunityPostModal');
        if (modal) modal.classList.add('hidden');
    }

    function previewEditCommunityMedia(input, type) {
        const notice = document.getElementById('editCommunityNewMediaNotice');
        if (input.files && input.files[0]) {
            notice.classList.remove('hidden');
            notice.textContent = `Selected new ${type}: ${input.files[0].name} (will replace existing media upon saving)`;
        }
    }

    function submitEditCommunityPost(event) {
        event.preventDefault();
        const form = document.getElementById('editCommunityPostForm');
        const communityId = document.getElementById('editCommunityId').value;
        const postId = document.getElementById('editCommunityPostId').value;
        const submitBtn = document.getElementById('editCommunitySubmitBtn');
        const origBtnText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i><span>Saving...</span>';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        const formData = new FormData(form);

        fetch(`/communities/${communityId}/posts/${postId}`, {
            method: 'POST', // Blade spoofed PUT
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to update community post.');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                closeEditCommunityPostModal();
                alert(data.message || 'Post updated successfully!');
                window.location.reload();
            } else {
                alert(data.error || 'Failed to update post.');
            }
        })
        .catch(err => {
            console.error('Community post update error:', err);
            form.submit();
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnText;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    // --- GLOBAL POST DELETION LOGIC ---
    function confirmDeleteTimelinePost(postId) {
        pendingDeletionConfig = {
            type: 'timeline',
            postId: postId,
            url: `/timeline/${postId}`
        };

        document.getElementById('deletePostModalTitle').textContent = 'Delete Story?';
        document.getElementById('deletePostModalMessage').textContent = 'Are you sure you want to delete this story? This will permanently remove it from your timeline along with all reactions and responses.';
        document.getElementById('deletePostConfirmationModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function confirmDeleteCommunityPost(communityId, postId) {
        pendingDeletionConfig = {
            type: 'community',
            communityId: communityId,
            postId: postId,
            url: `/communities/${communityId}/posts/${postId}`
        };

        document.getElementById('deletePostModalTitle').textContent = 'Delete Community Post?';
        document.getElementById('deletePostModalMessage').textContent = 'Are you sure you want to delete this post from the community? This action cannot be undone.';
        document.getElementById('deletePostConfirmationModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeDeletePostModal() {
        document.getElementById('deletePostConfirmationModal').classList.add('hidden');
        pendingDeletionConfig = null;
    }

    function executePostDeletion() {
        if (!pendingDeletionConfig) return;

        const btn = document.getElementById('deletePostConfirmBtn');
        const btnText = document.getElementById('deletePostConfirmBtnText');
        const origText = btnText.textContent;
        btn.disabled = true;
        btnText.textContent = 'Deleting...';

        const config = pendingDeletionConfig;

        fetch(config.url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Deletion failed.');
            return res.json();
        })
        .then(data => {
            closeDeletePostModal();
            // Smoothly remove the post card from DOM
            const card = document.getElementById(`post-${config.postId}`);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 300);
            }
            alert(data.message || 'Post deleted successfully.');
        })
        .catch(err => {
            console.error('Deletion error:', err);
            // Fallback via form submission
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = config.url;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            f.appendChild(csrf);
            const m = document.createElement('input');
            m.type = 'hidden';
            m.name = '_method';
            m.value = 'DELETE';
            f.appendChild(m);
            document.body.appendChild(f);
            f.submit();
        })
        .finally(() => {
            btn.disabled = false;
            btnText.textContent = origText;
        });
    }
</script>
