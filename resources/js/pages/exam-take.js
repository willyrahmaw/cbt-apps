/**
 * Halaman mengerjakan ujian - student exams take
 * Config: window.examTakeConfig { remainingSeconds, sessionId, logUrl, remainingUrl }
 */
(function () {
    const config = window.examTakeConfig || {};
    const remainingSecondsRef = { value: config.remainingSeconds ?? 0 };
    let remainingSeconds = remainingSecondsRef.value;
    const sessionId = config.sessionId;
    const logUrl = config.logUrl || `/student/session/${sessionId}/log`;
    const remainingUrl = config.remainingUrl || `/student/session/${sessionId}/remaining`;
    const essayTimers = {};
    const questionViewedAt = {};
    let tabWarningShown = false;
    let lastBlockWarning = 0;
    let splitScreenWarningShown = false;
    let windowBlurWarningShown = false;
    let fullscreenExitWarningShown = false;
    let wasInFullscreen = false;
    const initialViewportWidth = window.innerWidth || document.documentElement.clientWidth || 375;
    let currentQuestionIndex = 0;

    const A11Y = {
        storageKeyScale: 'cbt_a11y_font_scale',
        storageKeyContrast: 'cbt_a11y_high_contrast',
        minScale: 0.85,
        maxScale: 1.35,
        step: 0.1
    };

    function clamp(n, min, max) {
        return Math.min(max, Math.max(min, n));
    }

    function isEditableTarget(el) {
        if (!el) return false;
        if (el.isContentEditable) return true;
        const tag = (el.tagName || '').toUpperCase();
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
    }

    function getQuestionCount() {
        return document.querySelectorAll('.question-panel').length;
    }

    function getActivePanel() {
        return document.querySelector(`.question-panel[data-index="${currentQuestionIndex}"]`);
    }

    function focusFirstInPanel(index) {
        const panel = document.querySelector(`.question-panel[data-index="${index}"]`);
        if (!panel) return;
        try {
            panel.scrollIntoView({ block: 'start', behavior: 'smooth' });
        } catch (_) {}
        const essay = panel.querySelector('textarea[id^="essay-"]');
        if (essay) {
            essay.focus?.({ preventScroll: true });
            return;
        }
        const checked = panel.querySelector('input[type="radio"]:checked');
        const first = checked || panel.querySelector('input[type="radio"]');
        first?.focus?.({ preventScroll: true });
    }

    function applyFontScale(scale) {
        const s = clamp(Number(scale) || 1, A11Y.minScale, A11Y.maxScale);
        document.documentElement.style.setProperty('--cbt-font-scale', String(s));
        localStorage.setItem(A11Y.storageKeyScale, String(s));
    }

    function getFontScale() {
        const stored = Number(localStorage.getItem(A11Y.storageKeyScale));
        return clamp(Number.isFinite(stored) ? stored : 1, A11Y.minScale, A11Y.maxScale);
    }

    function applyHighContrast(enabled) {
        const on = !!enabled;
        document.documentElement.classList.toggle('cbt-high-contrast', on);
        localStorage.setItem(A11Y.storageKeyContrast, on ? '1' : '0');
        const btn = document.getElementById('cbt-contrast-toggle');
        if (btn) btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    }

    function getHighContrast() {
        return localStorage.getItem(A11Y.storageKeyContrast) === '1';
    }

    function initA11yControls() {
        applyFontScale(getFontScale());
        applyHighContrast(getHighContrast());

        document.getElementById('cbt-font-dec')?.addEventListener('click', () => {
            applyFontScale(Math.round((getFontScale() - A11Y.step) * 100) / 100);
        });
        document.getElementById('cbt-font-inc')?.addEventListener('click', () => {
            applyFontScale(Math.round((getFontScale() + A11Y.step) * 100) / 100);
        });
        document.getElementById('cbt-font-reset')?.addEventListener('click', () => applyFontScale(1));
        document.getElementById('cbt-contrast-toggle')?.addEventListener('click', () => applyHighContrast(!getHighContrast()));
    }

    function checkSplitScreen() {
        if (splitScreenWarningShown || initialViewportWidth > 768) return;
        const w = (window.visualViewport?.width ?? window.innerWidth) || window.innerWidth;
        if (w < initialViewportWidth * 0.55 && w < 320) {
            splitScreenWarningShown = true;
            logCheatingEvent('split_screen');
            window.Swal?.fire({ icon: 'warning', title: 'Pelanggaran Terdeteksi', text: 'Split screen atau multi-window terdeteksi. Gunakan layar penuh untuk ujian.' });
        }
    }
    window.addEventListener('resize', checkSplitScreen);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', checkSplitScreen);
    }

    function logCheatingEvent(event) {
        fetch(logUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
            body: JSON.stringify({ event: event })
        })
            .then(r => r.json().catch(() => ({})))
            .then(data => {
                if (data && data.terminated && data.redirect) {
                    window.Swal?.fire({ icon: 'error', title: 'Ujian Diakhiri', text: 'Anda melakukan pelanggaran yang mengakibatkan ujian diakhiri. Mintalah token baru ke pengawas untuk mengulang.' }).then(() => window.location.href = data.redirect);
                }
            })
            .catch(() => {});
    }

    const examPage = document.getElementById('exam-page');
    if (examPage) {
        examPage.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            logCheatingEvent('right_click');
            if (Date.now() - lastBlockWarning > 3000) {
                lastBlockWarning = Date.now();
                window.Swal?.fire({ icon: 'warning', title: 'Tidak Diizinkan', text: 'Klik kanan dinonaktifkan selama ujian.' });
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        const target = e.target;
        const isEssay = target.tagName === 'TEXTAREA' && target.id && target.id.startsWith('essay-');
        if (!isEssay && (e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x' || e.key === 'v')) {
            e.preventDefault();
            logCheatingEvent(e.key === 'v' ? 'paste_attempt' : 'copy_attempt');
            if (Date.now() - lastBlockWarning > 3000) {
                lastBlockWarning = Date.now();
                const msg = e.key === 'v' ? 'Paste dinonaktifkan selama ujian.' : 'Copy/cut dinonaktifkan selama ujian.';
                window.Swal?.fire({ icon: 'warning', title: 'Tidak Diizinkan', text: msg });
            }
            return;
        }
        const key = e.key.toLowerCase();
        const isPrintScreen = key === 'printscreen';
        const isPrintShortcut = (e.ctrlKey || e.metaKey) && key === 'p';
        if (isPrintScreen || isPrintShortcut) {
            e.preventDefault();
            logCheatingEvent(isPrintScreen ? 'screenshot_attempt' : 'print_attempt');
            if (Date.now() - lastBlockWarning > 3000) {
                lastBlockWarning = Date.now();
                const msg = isPrintScreen
                    ? 'Percobaan screenshot layar terdeteksi dan tidak diizinkan selama ujian.'
                    : 'Percobaan print / simpan halaman tidak diizinkan selama ujian.';
                window.Swal?.fire({ icon: 'warning', title: 'Peringatan', text: msg });
            }
        }

        // A11y / keyboard navigation (avoid interfering with typing)
        if (isEditableTarget(target)) return;

        const k = e.key;
        const keyLower = String(k || '').toLowerCase();
        const count = getQuestionCount();

        // Ctrl + ArrowLeft/ArrowRight: prev/next question (do NOT use Alt+Arrow on Windows - browser back/forward)
        if (e.ctrlKey && (k === 'ArrowLeft' || k === 'ArrowRight')) {
            e.preventDefault();
            const delta = k === 'ArrowRight' ? 1 : -1;
            window.goToQuestion?.(clamp(currentQuestionIndex + delta, 0, Math.max(0, count - 1)));
            return;
        }

        // Ctrl + Home / Ctrl + End: first / last question
        if (e.ctrlKey && (k === 'Home' || k === 'End')) {
            e.preventDefault();
            window.goToQuestion?.(k === 'Home' ? 0 : Math.max(0, count - 1));
            return;
        }

        // Ctrl + Shift + H: toggle high contrast
        if (e.ctrlKey && e.shiftKey && keyLower === 'h') {
            e.preventDefault();
            applyHighContrast(!getHighContrast());
            return;
        }

        // F: toggle fullscreen
        if (!e.ctrlKey && !e.metaKey && !e.altKey && keyLower === 'f') {
            e.preventDefault();
            window.toggleFullscreen?.();
            return;
        }

        // R: toggle ragu (if enabled)
        if (!e.ctrlKey && !e.metaKey && !e.altKey && keyLower === 'r') {
            const panel = getActivePanel();
            const qid = panel?.querySelector('.ragu-checkbox')?.dataset?.question;
            const cb = qid ? document.getElementById('ragu-' + qid) : null;
            if (cb && !cb.disabled) {
                e.preventDefault();
                cb.checked = !cb.checked;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            }
            return;
        }

        // Select MC option by A-E or 1-5
        const optionIndex =
            /^[1-5]$/.test(k) ? (parseInt(k, 10) - 1)
                : (keyLower >= 'a' && keyLower <= 'e' ? (keyLower.charCodeAt(0) - 97) : -1);

        if (optionIndex >= 0) {
            const panel = getActivePanel();
            const radios = panel?.querySelectorAll('input[type="radio"]') || [];
            const radio = radios[optionIndex];
            if (radio) {
                e.preventDefault();
                radio.checked = true;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });

    document.addEventListener('visibilitychange', function () {
        if (document.hidden && !tabWarningShown) {
            tabWarningShown = true;
            logCheatingEvent('tab_switch');
            window.Swal?.fire({ icon: 'warning', title: 'Peringatan', text: 'Tetap fokus di tab ujian selama mengerjakan.' });
        }
    });

    window.addEventListener('blur', function () {
        if (!document.hidden && !windowBlurWarningShown) {
            windowBlurWarningShown = true;
            logCheatingEvent('window_blur');
            window.Swal?.fire({ icon: 'warning', title: 'Peringatan', text: 'Jendela ujian kehilangan fokus. Pastikan ujian tetap berada di depan.' });
        }
    });

    document.addEventListener('fullscreenchange', function () {
        if (document.fullscreenElement) {
            wasInFullscreen = true;
        } else if (wasInFullscreen && !fullscreenExitWarningShown) {
            fullscreenExitWarningShown = true;
            logCheatingEvent('fullscreen_exit');
            window.Swal?.fire({ icon: 'warning', title: 'Peringatan', text: 'Mode layar penuh disarankan untuk kenyamanan ujian.' });
        }
    });

    function syncTimerFromServer() {
        fetch(remainingUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.remaining_seconds !== undefined) {
                    remainingSeconds = Math.max(0, data.remaining_seconds);
                    if (remainingSeconds <= 0) {
                        window.Swal?.fire({ icon: 'warning', title: 'Waktu Habis', text: 'Waktu ujian sudah berakhir.' }).then(() => document.getElementById(config.finishFormId || 'finish-form')?.submit());
                    }
                }
            }).catch(() => {});
    }
    setInterval(syncTimerFromServer, 60000);

    window.toggleFullscreen = function () {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    };
    document.addEventListener('fullscreenchange', () => {
        const icon = document.getElementById('fullscreen-icon');
        if (document.fullscreenElement) {
            wasInFullscreen = true;
            if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
        } else {
            if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>';
        }
    });

    function startTimer() {
        updateTimerDisplay();
        setInterval(() => {
            remainingSeconds--;
            updateTimerDisplay();
            if (remainingSeconds <= 0) {
                document.getElementById(config.finishFormId || 'finish-form')?.submit();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        const secs = remainingSeconds % 60;
        const display = document.getElementById('timer-display');
        if (!display) return;
        if (hours > 0) {
            display.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        } else {
            display.textContent = `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
        const timer = document.getElementById('timer');
        if (timer && remainingSeconds <= 300) timer.classList.add('animate-pulse');
    }

    function setNavStatusClass(btn) {
        const status = btn.dataset.status || 'unanswered';
        btn.classList.remove(
            'bg-slate-100', 'text-slate-500', 'border-slate-200',
            'bg-emerald-100', 'text-emerald-700', 'border-emerald-200',
            'bg-amber-100', 'text-amber-700', 'border-amber-200',
            'bg-indigo-100', 'text-indigo-600', 'border-indigo-300'
        );
        if (status === 'answered') {
            btn.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
        } else if (status === 'ragu') {
            btn.classList.add('bg-amber-100', 'text-amber-700', 'border-amber-200');
        } else {
            btn.classList.add('bg-slate-100', 'text-slate-500', 'border-slate-200');
        }
    }

    window.goToQuestion = function (index) {
        const max = Math.max(0, document.querySelectorAll('.question-panel').length - 1);
        const nextIndex = clamp(index, 0, max);
        currentQuestionIndex = nextIndex;
        const panels = document.querySelectorAll('.question-panel');
        const qid = document.querySelector(`.question-nav-btn[data-index="${nextIndex}"]`)?.dataset?.qid;
        if (qid && !questionViewedAt[qid]) questionViewedAt[qid] = Date.now() / 1000;
        panels.forEach((panel, i) => panel.classList.toggle('hidden', i !== nextIndex));
        document.querySelectorAll('.question-nav-btn').forEach(btn => {
            const isActive = parseInt(btn.dataset.index) === nextIndex;
            btn.classList.remove('ring-2', 'ring-indigo-400');
            setNavStatusClass(btn);
            btn.setAttribute('aria-current', isActive ? 'true' : 'false');
            if (isActive) {
                btn.classList.remove(
                    'bg-slate-100', 'text-slate-500', 'border-slate-200',
                    'bg-emerald-100', 'text-emerald-700', 'border-emerald-200',
                    'bg-amber-100', 'text-amber-700', 'border-amber-200'
                );
                btn.classList.add('bg-indigo-100', 'text-indigo-600', 'border-indigo-300', 'ring-2', 'ring-indigo-400');
            }
        });
        requestAnimationFrame(() => focusFirstInPanel(nextIndex));
    };

    function getTimeSpentSeconds(questionId) {
        const viewed = questionViewedAt[questionId];
        if (!viewed) return 0;
        return Math.round(Date.now() / 1000 - viewed);
    }

    function getRaguCount() {
        return document.querySelectorAll('.question-nav-btn[data-is-ragu="1"]').length;
    }

    window.tryFinishExam = function () {
        const raguCount = getRaguCount();
        if (raguCount > 0) {
            window.Swal?.fire({
                icon: 'warning',
                title: 'Masih Ada Ragu-Ragu',
                text: 'Masih ada ' + raguCount + ' soal yang ditandai ragu-ragu. Silakan tinjau dan pastikan jawaban Anda sebelum menyelesaikan ujian.'
            });
            return;
        }
        window.confirmAction?.(document.getElementById(config.finishFormId || 'finish-form'), 'Selesaikan Ujian?', 'Jawaban yang sudah tersimpan tidak bisa diubah lagi.', 'Ya, selesaikan', 'warning');
    };

    window.saveMcAnswer = function (sid, qid, aid, radio) {
        const label = radio.closest('label');
        const allLabels = label?.parentElement?.querySelectorAll('.answer-option');
        if (allLabels) allLabels.forEach(l => {
            l.classList.remove('border-indigo-500', 'bg-indigo-50');
            l.classList.add('border-slate-100');
        });
        if (label) {
            label.classList.remove('border-slate-100');
            label.classList.add('border-indigo-500', 'bg-indigo-50');
        }
        const raguCheck = document.getElementById('ragu-' + qid);
        if (raguCheck) raguCheck.removeAttribute('disabled');
        const isRagu = raguCheck && raguCheck.checked;
        markNavAnswered(qid, isRagu);
        const timeSpent = getTimeSpentSeconds(String(qid));
        fetch(`/student/session/${sid}/answer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
            body: JSON.stringify({ question_id: qid, answer_id: aid, is_ragu: isRagu, time_spent_seconds: timeSpent })
        }).then(async r => {
            const data = await r.json().catch(() => ({}));
            if (data.time_up) {
                if (data.terminated && data.redirect) {
                    window.Swal?.fire({ icon: 'error', title: 'Ujian Diakhiri', text: 'Waktu ujian sudah habis.' }).then(() => window.location.href = data.redirect);
                } else {
                    document.getElementById(config.finishFormId || 'finish-form')?.submit();
                }
            } else if (data.terminated && data.redirect) {
                window.Swal?.fire({ icon: 'error', title: 'Ujian Diakhiri', text: 'Ujian diakhiri oleh sistem.' }).then(() => window.location.href = data.redirect);
            } else if (r.status === 429) {
                window.Swal?.fire({ icon: 'warning', title: 'Terlalu cepat', text: 'Tunggu sebentar lalu coba lagi.' });
            } else if (!r.ok) {
                window.Swal?.fire({ icon: 'error', title: 'Gagal', text: data.error || 'Gagal menyimpan jawaban.' });
            }
        }).catch(() => {
            window.Swal?.fire({ icon: 'error', title: 'Kesalahan', text: 'Gagal menyimpan jawaban. Periksa koneksi internet.' });
        });
    };

    window.saveRagu = function (sid, qid, checkbox) {
        const panel = checkbox.closest('.question-panel');
        const checkedRadio = panel?.querySelector('input[type="radio"]:checked');
        if (!checkedRadio) return;
        const answerId = parseInt(checkedRadio.value, 10);
        const isRagu = checkbox.checked;
        const navBtn = document.querySelector(`.question-nav-btn[data-qid="${qid}"]`);
        if (navBtn) {
            navBtn.dataset.isRagu = isRagu ? '1' : '0';
            navBtn.dataset.status = isRagu ? 'ragu' : 'answered';
            setNavStatusClass(navBtn);
        }
        const timeSpent = getTimeSpentSeconds(String(qid));
        fetch(`/student/session/${sid}/answer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
            body: JSON.stringify({ question_id: qid, answer_id: answerId, is_ragu: isRagu, time_spent_seconds: timeSpent })
        }).then(async r => {
            const data = await r.json().catch(() => ({}));
            if (data.time_up) {
                if (data.terminated && data.redirect) window.location.href = data.redirect;
                else document.getElementById(config.finishFormId || 'finish-form')?.submit();
            } else if (data.terminated && data.redirect) window.location.href = data.redirect;
        }).catch(() => {});
    };

    function saveEssayAnswer(textarea) {
        const sid = textarea.dataset.session;
        const qid = textarea.dataset.question;
        const text = textarea.value.trim();
        if (!text) return;
        markNavAnswered(qid, false);
        const timeSpent = getTimeSpentSeconds(qid);
        fetch(`/student/session/${sid}/answer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
            body: JSON.stringify({ question_id: parseInt(qid), essay_text: text, time_spent_seconds: timeSpent })
        }).then(async res => {
            const data = await res.json().catch(() => ({}));
            if (res.ok) markNavAnswered(qid, false);
            else if (data.time_up) {
                if (data.terminated && data.redirect) window.location.href = data.redirect;
                else document.getElementById(config.finishFormId || 'finish-form')?.submit();
            } else if (data.terminated && data.redirect) window.location.href = data.redirect;
            else if (res.status === 429) window.Swal?.fire({ icon: 'warning', title: 'Terlalu cepat', text: 'Tunggu sebentar lalu coba lagi.' });
            else window.Swal?.fire({ icon: 'error', title: 'Gagal', text: data.error || 'Gagal menyimpan jawaban.' });
        }).catch(() => {
            window.Swal?.fire({ icon: 'error', title: 'Kesalahan', text: 'Gagal menyimpan. Periksa koneksi internet.' });
        });
    }

    function markNavAnswered(questionId, isRagu) {
        const navBtn = document.querySelector(`.question-nav-btn[data-qid="${questionId}"]`);
        if (navBtn) {
            navBtn.dataset.isRagu = isRagu ? '1' : '0';
            navBtn.dataset.status = isRagu ? 'ragu' : 'answered';
            setNavStatusClass(navBtn);
        }
    }

    document.querySelectorAll('textarea[id^="essay-"]').forEach(textarea => {
        textarea.addEventListener('input', function () {
            const qid = this.dataset.question;
            clearTimeout(essayTimers[qid]);
            essayTimers[qid] = setTimeout(() => saveEssayAnswer(this), 800);
        });
        textarea.addEventListener('blur', function () {
            if (this.value.trim()) saveEssayAnswer(this);
        });
    });

    startTimer();
    initA11yControls();
    window.goToQuestion(0);

    function updateWatermarkTime() {
        const formatted = new Date().toLocaleString('id-ID', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            hour12: false
        }).replace(',', '');
        document.querySelectorAll('.wm-time').forEach(el => { el.textContent = formatted; });
    }
    updateWatermarkTime();
    setInterval(updateWatermarkTime, 15000);

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.question-nav-btn').forEach(setNavStatusClass);
    });
})();
