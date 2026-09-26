{{-- Keeps timed practice/exam attempt screens visually stable on mobile while
     a student answers.

     1. touch-action: manipulation — options auto-advance in place, so a student
        answering quickly taps the same spot twice within the browser's
        double-tap window. Mobile browsers (iOS Safari especially, which ignores
        maximum-scale) treat that as a double-tap zoom/scroll gesture and pan the
        viewport, which then eases back. This disables only that gesture —
        normal scrolling and pinch-zoom still work.

     2. ApexStable.reserveHeight() — the question box sizes to its content, so
        moving from a 3-row sum to a 5-row sum resized the box and pushed the
        answer options up/down. It measures every question in the attempt once
        and pins the box's min-height to the tallest, so the options never move
        between questions (the space for the digits is kept, never reduced). --}}
<style>html { touch-action: manipulation; }</style>
<script>
window.ApexStable = {
    reserveHeight(content, htmls) {
        const box = content?.parentElement;
        if (!box || !htmls.length) return;

        const measure = () => {
            const cs = getComputedStyle(box);
            const probe = document.createElement('div');
            probe.className = content.className;
            probe.style.cssText = content.style.cssText;
            probe.style.position = 'absolute';
            probe.style.visibility = 'hidden';
            probe.style.pointerEvents = 'none';
            probe.style.left = '-9999px';
            probe.style.width = (box.clientWidth - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight)) + 'px';
            document.body.appendChild(probe);

            let tallest = 0;
            for (const html of htmls) {
                probe.innerHTML = html;
                tallest = Math.max(tallest, probe.offsetHeight);
            }
            probe.remove();

            // Never below the design's own min-height (e.g. min-h-[170px]).
            box.style.minHeight = '';
            const designMin = parseFloat(cs.minHeight) || 0;
            const chrome = parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom)
                         + parseFloat(cs.borderTopWidth) + parseFloat(cs.borderBottomWidth);
            box.style.minHeight = Math.max(designMin, Math.ceil(tallest + chrome)) + 'px';
        };

        // A shorter question should sit centred in the reserved space, as it
        // already does in the flex-centred boxes, not stuck to the top.
        if (getComputedStyle(box).display !== 'flex') {
            Object.assign(box.style, { display: 'flex', flexDirection: 'column', justifyContent: 'center' });
        }

        measure();
        // Re-measure once web fonts settle and when the width changes (rotation).
        document.fonts?.ready.then(measure);
        let lastWidth = window.innerWidth;
        window.addEventListener('resize', () => {
            if (window.innerWidth !== lastWidth) { lastWidth = window.innerWidth; measure(); }
        });
    },
};
</script>
