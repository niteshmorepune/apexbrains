{{-- Reusable browser text-to-speech for practice/exam questions.
     Exposes window.ApexSpeak.speak(text) using a female en-IN voice when one is
     available on the student's device. Operators are read as words so a sum like
     "12 + 34 − 5" is dictated naturally. --}}
<script>
window.ApexSpeak = (function () {
    var femaleVoice = null;
    function choose() {
        if (!window.speechSynthesis) return;
        var voices = window.speechSynthesis.getVoices();
        if (!voices.length) return;
        var wanted = ['female', 'zira', 'heera', 'google uk english female', 'samantha'];
        femaleVoice =
            voices.find(function (v) { return /en[-_]?in/i.test(v.lang) && /female|heera/i.test(v.name); }) ||
            voices.find(function (v) { return wanted.some(function (w) { return v.name.toLowerCase().includes(w); }); }) ||
            voices.find(function (v) { return /^en/i.test(v.lang); }) ||
            voices[0] || null;
    }
    if (window.speechSynthesis) {
        choose();
        window.speechSynthesis.onvoiceschanged = choose;
    }
    function spoken(text) {
        return String(text == null ? '' : text)
            .replace(/[\n\r]+/g, ' , ')
            .replace(/[×x*]/g, ' multiplied by ')
            .replace(/[÷\/]/g, ' divided by ')
            .replace(/[−–\-]/g, ' less ')
            .replace(/\+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }
    return {
        speak: function (text) {
            if (!window.speechSynthesis) return;
            var phrase = spoken(text);
            if (!phrase) return;
            window.speechSynthesis.cancel();
            var u = new SpeechSynthesisUtterance(phrase);
            u.rate = 0.9;
            u.lang = 'en-IN';
            if (femaleVoice) u.voice = femaleVoice;
            window.speechSynthesis.speak(u);
        }
    };
})();
</script>
