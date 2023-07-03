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
<x-widget :title="$title" x-data="timer{{$attrs['id']}}(new Date(Date.UTC(2023, 4, 6, 16, 30, 0, 0)))" x-init="init();">
    <p class="text-xs">
    @if(defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en')
        One day, we would like to move to the United States, and the only way to do it legally is to participate in the Diversity Visa program.
        We have very low chances of winning, but we will keep trying until we do win 💪.
    @else
        Odata si odata ne-am dori sa ne mutam in Statele Unite si singura cale ca sa facem asta in mod legal este sa participam la Loteria Vizelor.
        Avem sanse foarte mici de castig, dar vom continua sa incercam pana castigam 💪.
    @endif
        </p>
    <ul>
        <li>
            <strong>DV-2022</strong> - 🔴 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Did not win' : 'Nu am castigat' }}
        </li>
        <li>
            <strong>DV-2023</strong> - ❌ {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'No luck' : 'Fara noroc' }}
        </li>
        <li>
            <strong>DV-2024</strong> - 🚨 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'Nope' : 'Nup' }}
        </li>
        <li>
            <strong>DV-2025</strong> - 🕧 {{ defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en' ? 'We find out in ' : 'Aflam in ' }}
            <span x-text="time().days"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'zile' : 'days' }},</span>
            <span x-text="time().hours"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'ore' : 'hours' }},</span>
            <span x-text="time().minutes"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'minute' : 'minutes' }},</span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'si' : 'and' }} </span>
            <span x-text="time().seconds"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'secunde' : 'seconds' }}</span>
        </li>
    </ul>
</x-widget>
