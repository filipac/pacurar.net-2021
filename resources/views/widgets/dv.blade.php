<x-widget :title="$title"
          x-data="timer{{$attrs['id']}}(new Date(Date.UTC(2025, 2, 31, 18, 30, 0, 0)))"
          x-init="init();"
>
    <p class="text-xs">
        @if(defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en')
            We started timidly, with low hopes but confident that if it's God's will, one day we will move to the United States of America.
            We participated in the visa lottery for 4 consecutive years and on May 4th,
            2024 we found out that we were selected for a possible immigrant visa in 🇺🇸.
            We owe everything to God, He will help us until the end.
        @else
            Am inceput timid, cu sperante mici dar increzatori ca daca este voia lui Dumnezeu intr-o zi vom ajunge
            sa ne mutam in Statele Unite ale Americii. Am participat la loteria vizelor 4 ani consecutiv
            si in 4 mai 2024 am aflat ca am fost alesi pentru o posibila viza de imigrant in 🇺🇸.
            Lui Dumnezeu ii datoram totul, El ne va ajuta pana la capat.
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
            ✅ {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'We were amongst the 1% selected' : 'Am fost printre cei 1% selectati' }}
        </li>
        <li>
            <strong>
                🕧 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Interview ETA' : 'Data interviului (approx)' }}
            </strong> - 🕧 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Left: ' : 'A rams: ' }}
            <span x-text="time().days"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'zile' : 'days' }},</span>
            <span x-text="time().hours"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'ore' : 'hours' }},</span>
            <span x-text="time().minutes"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'minute' : 'minutes' }},</span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'si' : 'and' }} </span>
            <span x-text="time().seconds"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'secunde' : 'seconds' }}</span>
            <ul>
                <li>
                    🏥{{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Medical' : 'Vizita medicala' }}
                </li>
                <li>
                    👮🏻‍{{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Interview' : 'Interviul' }}
                </li>
                <li>
                    ✈ {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Flight' : 'Zborul' }}
                </li>
            </ul>
        </li>
    </ul>
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
