<x-widget :title="$title"
          x-data="timer{{$attrs['id']}}(new Date(Date.UTC(2026, 4, 2, 17, 30, 0, 0)))"
          x-init="init();"
>
    <p class="text-xs">
        @if(defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en')
            We started timidly, with low hopes but confident that if it's God's will, one day we will move to the United States of America.
            We participated in the visa lottery for 4 consecutive years and on May 4th,
            2024 we found out that we were selected for a possible immigrant visa in 🇺🇸.
            Unfortunately, even though we were selected, in 2025 fiscal year they stopped at the case number 23.000 for Europe, and we had 29.000. So we did not get a green card... we'll try again for DV2027.
        @else
            Am inceput timid, cu sperante mici dar increzatori ca daca este voia lui Dumnezeu intr-o zi vom ajunge
            sa ne mutam in Statele Unite ale Americii. Am participat la loteria vizelor 4 ani consecutiv
            si in 4 mai 2024 am aflat ca am fost alesi pentru o posibila viza de imigrant in 🇺🇸.
            Din pacate, chiar dacaam fost alesi, in anul fiscal 2025 s-au oprit la numarul de caz 23.000 pentru Europa, iar noi aveam 29.000. Deci nu am primit green card... vom incerca din nou pentru DV2027
        @endif
    </p>
    <ul>
        <li>
            <strong>DV-2022</strong> -
            🔴 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Did not win' : 'Nu am castigat' }}
        </li>
        <li>
            <strong>DV-2023</strong> -
            ❌ {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'No luck' : 'Fara noroc' }}
        </li>
        <li>
            <strong>DV-2024</strong> -
            🚨 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Nope' : 'Nup' }}
        </li>
        <li>
            <strong>DV-2025</strong> -
            ❌ {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'We were amongst the 1% selected, but did not get a green card...' : 'Am fost printre cei 1% selectati, dar nu am primit green card...' }}
        </li>
        <li>
            <strong>DV-2026</strong> -
            ❌ {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Again not selected...' : 'Din nou nu am fost selectati...' }}
        </li>

        <li>
            <strong>DV-2027</strong> - 🕧 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'We find out in ' : 'Aflam in ' }}
            <span x-text="time().days"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'zile' : 'days' }},</span>
            <span x-text="time().hours"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'ore' : 'hours' }},</span>
            <span x-text="time().minutes"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'minute' : 'minutes' }},</span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'si' : 'and' }} </span>
            <span x-text="time().seconds"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'secunde' : 'seconds' }}</span>
            <strong>({{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'application in October 2025, results in May 2nd 2026' : 'aplicam in octombrie 2025, rezultatele sunt in 2 mai 2026' }})</strong>
        </li>
</x-widget>
<script>
    function timer{{$attrs['id']}}(expiry) {
        return {
            expiry: expiry,
            remaining: null,
            init() {
                this.setRemaining()
                setInterval(() => {
                    this.setRemaining()
                }, 1000)
            },
            setRemaining() {
                const diff = this.expiry - new Date().getTime()
                this.remaining = parseInt(diff / 1000)
            },
            days() {
                return {
                    value: this.remaining / 86400,
                    remaining: this.remaining % 86400,
                }
            },
            hours() {
                return {
                    value: this.days().remaining / 3600,
                    remaining: this.days().remaining % 3600,
                }
            },
            minutes() {
                return {
                    value: this.hours().remaining / 60,
                    remaining: this.hours().remaining % 60,
                }
            },
            seconds() {
                return {
                    value: this.minutes().remaining,
                }
            },
            format(value) {
                // if value is a single digit, add a 0 in front, otherwise return the value
                return parseInt(value) < 10 ? '0' + parseInt(value) : parseInt(value)
            },
            time() {
                return {
                    days: this.format(this.days().value),
                    hours: this.format(this.hours().value),
                    minutes: this.format(this.minutes().value),
                    seconds: this.format(this.seconds().value),
                }
            },
        }
    }
</script>
