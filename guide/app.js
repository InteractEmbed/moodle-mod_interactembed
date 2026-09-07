/* global window, document */
(function() {
    'use strict';

    let interactEmbedNonce = '';
    window.addEventListener('message', event => {
        if (event.source === window.parent && event.data && event.data.type === 'interactembed:init' &&
                event.data.version === 1 && typeof event.data.nonce === 'string') {
            interactEmbedNonce = event.data.nonce;
        }
    });

    const course = window.INTERACTEMBED_COURSE;
    const app = document.getElementById('course-app');
    const slideRoot = document.getElementById('slide');
    const viewport = document.getElementById('course-main');
    const languageSelect = document.getElementById('language-select');
    let fitFrame = 0;
    const state = {
        language: preferredLanguage(),
        current: 0,
        visited: new Set([0]),
        answers: {},
        providers: new Set(),
        ratios: new Set(['16x9']),
    };

    const icons = {
        understand: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-4.2 12.6c.75.56 1.2 1.35 1.2 2.2V18h6v-1.2c0-.85.45-1.64 1.2-2.2A7 7 0 0 0 12 2Zm-2 18h4v2h-4z"/></svg>',
        create: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3h-4.18A3 3 0 0 0 12 1a3 3 0 0 0-2.82 2H5a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm4 12h-3v3h-2v-3H8v-2h3v-3h2v3h3z"/></svg>',
        adapt: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10v2H7zM4 7h16v10H4zm2 2v6h12V9zm1 11h10v2H7z"/></svg>',
        home: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 9 8h-3v9h-5v-6h-2v6H6v-9H3z"/></svg>',
        dashboard: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8v8H3zm10 0h8v5h-8zM3 13h8v8H3zm10-3h8v11h-8z"/></svg>',
        course: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h13a3 3 0 0 1 3 3v15H6a3 3 0 0 1-3-3V4a1 1 0 0 1 1-1Zm2 13a3 3 0 0 0-1 .17V18a1 1 0 0 0 1 1h12v-3z"/></svg>',
        category: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v2H3zm0 6h18l-2 9H5z"/></svg>',
        shield: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.1 3.4 9.8 8 11 4.6-1.2 8-5.9 8-11V5zm-1 14-4-4 1.4-1.4 2.6 2.6 5.6-5.6L18 9z"/></svg>',
        check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-2 15-4-4 1.4-1.4 2.6 2.6 6.6-6.6L18 9z"/></svg>',
    };

    function preferredLanguage() {
        const requestedLanguage = new URLSearchParams(window.location.search).get('lang');
        if (requestedLanguage && course[requestedLanguage]) {
            return requestedLanguage;
        }
        const browserLanguage = (navigator.language || 'fr').slice(0, 2).toLowerCase();
        return course[browserLanguage] ? browserLanguage : 'fr';
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;',
        })[character]);
    }

    function ui(key) {
        return course[state.language].ui[key];
    }

    function slides() {
        return course[state.language].slides;
    }

    function commonIntro(slide) {
        return `<p class="eyebrow">${escapeHtml(slide.eyebrow)}</p>
            <h2 tabindex="-1">${escapeHtml(slide.title)}</h2>
            <p class="lead">${escapeHtml(slide.lead)}</p>`;
    }

    function renderHero(slide) {
        const tags = slide.tags.map(tag => `<span class="tag">${icons.check}${escapeHtml(tag)}</span>`).join('');
        return `<div class="slide-grid">
            <div>
                <p class="eyebrow">${escapeHtml(slide.eyebrow)}</p>
                <h1 tabindex="-1">${escapeHtml(slide.title)}</h1>
                <p class="lead">${escapeHtml(slide.lead)}</p>
                <div class="tag-row">${tags}</div>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <div class="browser-card">
                    <div class="browser-bar"><i></i><i></i><i></i></div>
                    <div class="browser-screen"><div class="browser-screen-inner">
                        <div class="browser-lines"><span></span><span></span><span></span></div>
                        <div class="play-orbit"><svg viewBox="0 0 24 24"><path d="m8 5 11 7-11 7z"/></svg></div>
                    </div></div>
                </div>
            </div>
        </div>`;
    }

    function renderObjectives(slide) {
        const names = ['understand', 'create', 'adapt'];
        const cards = slide.cards.map((card, index) => `<article class="info-card">
            <span class="info-card__icon">${icons[names[index]]}</span>
            <h3>${escapeHtml(card[0])}</h3><p>${escapeHtml(card[1])}</p>
        </article>`).join('');
        return `<div>${commonIntro(slide)}<div class="card-grid">${cards}</div>
            <p class="microcopy">${escapeHtml(slide.note)}</p></div>`;
    }

    function renderCompare(slide) {
        return `<div>${commonIntro(slide)}<div class="compare">
            <section class="compare-panel"><header><span class="step-number">A</span><h3>${escapeHtml(slide.leftTitle)}</h3></header>
                <ul>${slide.leftItems.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul></section>
            <section class="compare-panel compare-panel--focus"><header><span class="step-number">B</span><h3>${escapeHtml(slide.rightTitle)}</h3></header>
                <ul>${slide.rightItems.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul></section>
        </div><div class="feedback is-visible is-correct">${escapeHtml(slide.takeaway)}</div></div>`;
    }

    function renderContexts(slide) {
        const contextIcons = ['home', 'dashboard', 'course', 'category'];
        const nodes = slide.contexts.map((context, index) => `<div class="context-node">${icons[contextIcons[index]]}
            <strong>${escapeHtml(context[0])}</strong><span class="microcopy">${escapeHtml(context[1])}</span></div>`).join('');
        return `<div class="slide-grid"><div>${commonIntro(slide)}
            <div class="activity-box"><div class="activity-box__title"><span>${escapeHtml(ui('reflection'))}</span>${escapeHtml(slide.prompt)}</div></div>
            </div><div class="hero-visual"><div class="context-map">${nodes}</div></div></div>`;
    }

    function renderSteps(items) {
        return `<div class="sequence">${items.map((item, index) => `<div class="sequence-item"><b>${index + 1}</b><span>${escapeHtml(item)}</span></div>`).join('')}</div>`;
    }

    function renderChoice(slide, slideIndex) {
        const selected = state.answers[slideIndex];
        const choices = slide.options.map(option => `<button type="button" class="choice-card" data-choice="${escapeHtml(option[0])}"
            aria-pressed="${selected === option[0]}"><strong>${escapeHtml(option[1])}</strong><small>${escapeHtml(option[2])}</small></button>`).join('');
        return `<div class="slide-grid"><div>${commonIntro(slide)}${renderSteps(slide.steps)}</div>
            <div class="activity-box"><div class="activity-box__title"><span>${escapeHtml(ui('activity'))}</span>${escapeHtml(slide.question)}</div>
                <div class="choice-list">${choices}</div>${feedback(slide, selected)}</div></div>`;
    }

    function renderProviders(slide) {
        const cards = slide.providers.map(provider => `<button type="button" class="provider-card" data-provider="${escapeHtml(provider[1])}"
            aria-pressed="${state.providers.has(provider[1])}"><span class="provider-badge">${escapeHtml(provider[0])}</span>
            <span><strong>${escapeHtml(provider[1])}</strong><small>${escapeHtml(provider[2])}</small></span></button>`).join('');
        const done = state.providers.size >= 3 ? `<div class="feedback is-visible is-correct">${escapeHtml(slide.done)}</div>` : '';
        return `<div>${commonIntro(slide)}<p class="microcopy">${escapeHtml(slide.instruction)} (${state.providers.size}/3)</p>
            <div class="provider-grid">${cards}</div>${done}</div>`;
    }

    function renderRatio(slide) {
        const active = state.activeRatio || '16x9';
        const current = slide.ratios.find(ratio => ratio[0] === active) || slide.ratios[0];
        const controls = slide.ratios.map(ratio => `<button type="button" class="pill-button" data-ratio="${ratio[0]}"
            data-css-ratio="${ratio[2]}" aria-pressed="${ratio[0] === active}">${ratio[1]}</button>`).join('');
        const done = state.ratios.size >= 2 ? `<div class="feedback is-visible is-correct">${escapeHtml(slide.done)}</div>` : '';
        return `<div class="slide-grid slide-grid--reverse"><div><div class="ratio-demo"><div class="ratio-screen" style="--demo-ratio:${current[2]}">
            <span>${escapeHtml(current[1])}</span></div></div><div class="ratio-controls">${controls}</div></div>
            <div>${commonIntro(slide)}<p class="microcopy">${escapeHtml(slide.instruction)} (${state.ratios.size}/2)</p>
            <ul class="check-list">${slide.tips.map(tip => `<li>${escapeHtml(tip)}</li>`).join('')}</ul>${done}</div></div>`;
    }

    function renderSecurity(slide, slideIndex) {
        const selected = state.answers[slideIndex];
        const layers = slide.layers.map(layer => `<div class="security-layer">${icons.shield}<span><strong>${escapeHtml(layer[0])}</strong>
            <small>${escapeHtml(layer[1])}</small></span></div>`).join('');
        const choices = slide.options.map(option => `<button type="button" class="choice-card" data-choice="${option[0]}"
            aria-pressed="${selected === option[0]}"><strong>${escapeHtml(option[1])}</strong><small>${escapeHtml(option[2])}</small></button>`).join('');
        return `<div class="slide-grid slide-grid--reverse"><div class="hero-visual"><div class="security-stack">${layers}</div></div>
            <div>${commonIntro(slide)}<div class="activity-box"><div class="activity-box__title"><span>${escapeHtml(ui('activity'))}</span>${escapeHtml(slide.question)}</div>
            <div class="choice-list">${choices}</div>${feedback(slide, selected)}</div></div></div>`;
    }

    function renderEmbed(slide, slideIndex) {
        const selected = state.answers[slideIndex];
        const choices = slide.options.map(option => `<button type="button" class="choice-card" data-choice="${option[0]}"
            aria-pressed="${selected === option[0]}"><strong>${escapeHtml(option[1])}</strong><small>${escapeHtml(option[2])}</small></button>`).join('');
        return `<div class="slide-grid"><div>${commonIntro(slide)}${renderSteps(slide.steps)}</div>
            <div class="activity-box"><div class="activity-box__title"><span>${escapeHtml(ui('activity'))}</span>${escapeHtml(slide.question)}</div>
            <div class="choice-list">${choices}</div>${feedback(slide, selected)}</div></div>`;
    }

    function feedback(slide, selected) {
        if (!selected) {
            return '<div class="feedback" aria-live="polite"></div>';
        }
        const correct = selected === slide.correct;
        if (correct) {
            return `<div class="feedback is-visible is-correct" aria-live="polite">${escapeHtml(slide.success)}</div>`;
        }
        return `<div class="feedback is-visible is-incorrect" aria-live="polite">
            <strong>${escapeHtml(ui('retry'))}</strong> ${escapeHtml(slide.failure)}</div>`;
    }

    function renderChallenge(slide, slideIndex) {
        const questions = slide.questions.map((question, questionIndex) => {
            const key = `${slideIndex}:${question.id}`;
            const selected = state.answers[key];
            const answerClass = selected ? (selected === question.correct ? ' is-correct-answer' : ' is-wrong-answer') : '';
            const options = question.options.map(option => `<button type="button" class="pill-button${selected === option[0] ? answerClass : ''}" data-question="${question.id}"
                data-answer="${option[0]}" aria-pressed="${selected === option[0]}">${escapeHtml(option[1])}</button>`).join('');
            return `<div class="activity-box"><div class="activity-box__title"><span>${questionIndex + 1}</span>${escapeHtml(question.prompt)}</div>
                <div class="ratio-controls">${options}</div></div>`;
        }).join('');
        const complete = slide.questions.every(question => state.answers[`${slideIndex}:${question.id}`]);
        const allCorrect = slide.questions.every(question => state.answers[`${slideIndex}:${question.id}`] === question.correct);
        const result = complete ? `<div class="feedback is-visible ${allCorrect ? 'is-correct' : 'is-incorrect'}">
            ${escapeHtml(allCorrect ? slide.feedback : ui('retry'))}</div>` : '';
        return `<div class="slide-grid"><div>${commonIntro(slide)}<div class="scenario"><div class="scenario-person">
            <span class="avatar">S</span><h3>Sofia</h3></div><p>${escapeHtml(slide.scenario)}</p></div></div>
            <div>${questions}${result}</div></div>`;
    }

    function score() {
        let correct = 0;
        let total = 0;
        slides().forEach((slide, index) => {
            if (slide.correct) {
                total += 1;
                if (state.answers[index] === slide.correct) {
                    correct += 1;
                }
            }
            if (slide.type === 'challenge') {
                slide.questions.forEach(question => {
                    total += 1;
                    if (state.answers[`${index}:${question.id}`] === question.correct) {
                        correct += 1;
                    }
                });
            }
        });
        return {correct, total, percent: total ? Math.round(correct / total * 100) : 0};
    }

    function renderComplete(slide) {
        const result = score();
        window.parent.postMessage({
            type: 'interactembed:progress',
            version: 1,
            status: 'completed',
            score: result.correct,
            scoremax: result.total,
            nonce: interactEmbedNonce,
        }, '*');
        return `<div class="slide-grid"><div>${commonIntro(slide)}
            <ul class="check-list">${slide.checklist.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul>
            <p class="feedback is-visible is-correct">${escapeHtml(slide.closing)}</p></div>
            <div><div class="completion-visual">${icons.check}</div><div class="score-panel">
                <div class="score-ring" style="--score:${result.percent}%"><strong>${result.percent}%</strong></div>
                <div><h3>${escapeHtml(ui('score'))}</h3><p class="microcopy">${result.correct} ${escapeHtml(ui('of'))} ${result.total}</p></div>
            </div></div></div>`;
    }

    function renderCurrent() {
        const slide = slides()[state.current];
        const renderers = {
            hero: () => renderHero(slide),
            objectives: () => renderObjectives(slide),
            compare: () => renderCompare(slide),
            contexts: () => renderContexts(slide),
            choice: () => renderChoice(slide, state.current),
            providers: () => renderProviders(slide),
            ratio: () => renderRatio(slide),
            security: () => renderSecurity(slide, state.current),
            embed: () => renderEmbed(slide, state.current),
            challenge: () => renderChallenge(slide, state.current),
            complete: () => renderComplete(slide),
        };

        slideRoot.innerHTML = `<div class="slide-fit">${renderers[slide.type]()}</div>`;
        updateInterface();
        bindSlideInteractions();
        fitCurrentSlide();
        viewport.scrollTop = 0;
        const heading = slideRoot.querySelector('h1, h2');
        if (heading) {
            window.setTimeout(() => heading.focus({preventScroll: true}), 20);
        }
    }

    /**
     * Scales dense desktop slides just enough to fit between the fixed header and footer.
     * Mobile layouts retain ordinary scrolling so text never becomes unreadably small.
     */
    function fitCurrentSlide() {
        window.cancelAnimationFrame(fitFrame);
        fitFrame = window.requestAnimationFrame(() => {
            const content = slideRoot.querySelector('.slide-fit');
            if (!content) {
                return;
            }
            content.removeAttribute('style');
            slideRoot.classList.remove('slide--fitted');
            if (window.matchMedia('(max-width: 640px)').matches) {
                return;
            }

            const slidestyle = window.getComputedStyle(slideRoot);
            const verticalpadding = parseFloat(slidestyle.paddingTop) + parseFloat(slidestyle.paddingBottom);
            const available = Math.max(1, viewport.clientHeight - verticalpadding);
            let required = content.scrollHeight;
            let scale = Math.min(1.1, available / required);

            if (Math.abs(scale - 1) < .005) {
                return;
            }

            // Compensate the transformed width so the fitted content still fills the player.
            for (let pass = 0; pass < 2; pass += 1) {
                content.style.width = `${100 / scale}%`;
                required = content.scrollHeight;
                scale = Math.min(1.1, available / required);
            }
            content.style.width = `${100 / scale}%`;
            scale = Math.min(scale, available / content.scrollHeight);
            content.style.transform = `scale(${scale})`;
            slideRoot.classList.add('slide--fitted');
        });
    }

    function updateInterface() {
        const data = course[state.language];
        document.documentElement.lang = state.language;
        document.title = `InteractEmbed — ${data.ui.courseType}`;
        languageSelect.value = state.language;
        document.querySelectorAll('[data-ui]').forEach(element => {
            const key = element.dataset.ui;
            if (data.ui[key]) {
                element.textContent = data.ui[key];
            }
        });
        document.querySelector('[data-ui="current"]').textContent = state.current + 1;
        document.querySelector('[data-ui="total"]').textContent = slides().length;
        document.querySelector('[data-ui="progress"]').style.width = `${(state.current + 1) / slides().length * 100}%`;
        document.querySelector('[data-action="previous"]').disabled = state.current === 0;
        document.querySelector('[data-ui="next"]').textContent = state.current === slides().length - 1 ? ui('finish') : ui('next');
        document.querySelector('[data-ui="dots"]').setAttribute('aria-label', ui('navigation'));
        renderDots();
    }

    function renderDots() {
        const root = document.querySelector('[data-ui="dots"]');
        root.innerHTML = slides().map((slide, index) => `<button type="button" data-slide="${index}"
            class="${state.visited.has(index) ? 'is-visited' : ''}" ${index === state.current ? 'aria-current="step"' : ''}
            aria-label="${escapeHtml(ui('step'))} ${index + 1}: ${escapeHtml(slide.title)}"></button>`).join('');
    }

    function bindSlideInteractions() {
        slideRoot.querySelectorAll('[data-choice]').forEach(button => {
            button.addEventListener('click', () => {
                state.answers[state.current] = button.dataset.choice;
                renderCurrent();
            });
        });
        slideRoot.querySelectorAll('[data-provider]').forEach(button => {
            button.addEventListener('click', () => {
                state.providers.add(button.dataset.provider);
                renderCurrent();
            });
        });
        slideRoot.querySelectorAll('[data-ratio]').forEach(button => {
            button.addEventListener('click', () => {
                state.activeRatio = button.dataset.ratio;
                state.ratios.add(button.dataset.ratio);
                renderCurrent();
            });
        });
        slideRoot.querySelectorAll('[data-question]').forEach(button => {
            button.addEventListener('click', () => {
                state.answers[`${state.current}:${button.dataset.question}`] = button.dataset.answer;
                renderCurrent();
            });
        });
    }

    function goTo(index) {
        state.current = Math.max(0, Math.min(slides().length - 1, index));
        state.visited.add(state.current);
        renderCurrent();
    }

    function restart() {
        state.current = 0;
        state.visited = new Set([0]);
        state.answers = {};
        state.providers = new Set();
        state.ratios = new Set(['16x9']);
        state.activeRatio = '16x9';
        renderCurrent();
    }

    document.querySelector('[data-action="previous"]').addEventListener('click', () => goTo(state.current - 1));
    document.querySelector('[data-action="next"]').addEventListener('click', () => {
        if (state.current === slides().length - 1) {
            restart();
        } else {
            goTo(state.current + 1);
        }
    });
    document.querySelector('[data-action="home"]').addEventListener('click', event => {
        event.preventDefault();
        goTo(0);
    });
    document.querySelector('[data-ui="dots"]').addEventListener('click', event => {
        const button = event.target.closest('[data-slide]');
        if (button) {
            goTo(Number(button.dataset.slide));
        }
    });
    languageSelect.addEventListener('change', () => {
        state.language = languageSelect.value;
        renderCurrent();
    });
    document.addEventListener('keydown', event => {
        const editing = ['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON'].includes(document.activeElement.tagName);
        if (editing || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }
        if (event.key === 'ArrowRight') {
            goTo(state.current + 1);
        } else if (event.key === 'ArrowLeft') {
            goTo(state.current - 1);
        }
    });
    window.addEventListener('resize', fitCurrentSlide);

    renderCurrent();
    app.setAttribute('aria-busy', 'false');
}());
