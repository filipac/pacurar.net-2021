<script>
    function timer(expiry) {
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
                return ('0' + parseInt(value)).slice(-2)
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
<div class="bg-white p-6 h-full flex flex-col items-center justify-center relative">
    <div class="flex justify-center">
        <img src="{{ get_stylesheet_directory_uri() }}/public/images/toppng.com-usa-waving-flag-467x600.png" class="w-1/2" />
    </div>
    <div class="flex flex-col items-center" x-data="timer(new Date(Date.UTC(2022, 4, 7, 16, 30, 0, 0)))" x-init="init();">
        <div class="mb-4">
            <strong class="font-bold">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Au mai ramas' : 'There\'s only' }}</strong>
        </div>
        <div x-show="time().days > 0">
            <span x-text="time().days"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'de zile' : 'days' }},</span>
        </div>
        <div>
            <span x-text="time().hours"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'ore' : 'hours' }},</span>
        </div>
        <div>
            <span x-text="time().minutes"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'minute' : 'minutes' }},</span>
        </div>
        <div>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'si' : 'and' }} </span>
            <span x-text="time().seconds"></span>
            <span>{{ ICL_LANGUAGE_CODE == 'ro' ? 'de secunde' : 'seconds' }}</span>
        </div>
        <div class="text-center mt-4">
            <strong class="font-bold">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Pana aflam daca am fost extrasi la Diversity Visa 2023.' : 'Until we find out if we won Diversity Visa 2023.' }}</strong>
        </div>
    </div>

</div>
