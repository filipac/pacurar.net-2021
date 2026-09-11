# Theme styles

`app.scss` is the Sass entry point. It loads partials with `@use`, in cascade
order: foundations, shared components and layouts, page styles, then geek-mode
overrides. Keep that order when adding modules.

## Where styles belong

| Location | Responsibility |
| --- | --- |
| `base/_typography.scss` | Fonts, heading defaults, and shared text helpers |
| `base/_document.scss` | Document background, icon sizing, focus states, and skip link |
| `components/_content.scss` | WordPress content, embeds, galleries, quotes, and code blocks |
| `components/_pagination.scss` | Archive/search pagination and search highlighting |
| `components/_comments.scss` | WordPress comment list and form defaults |
| `components/_reading-comments.scss` | Comment presentation on single posts |
| `components/_widgets.scss` | Sidebar widgets and metadata |
| `components/_buttons.scss` | Shared buttons and journal call to action |
| `components/_post-card.scss` | Post cards, notes, stories, and wide card variants |
| `layout/_navigation.scss` | Desktop/mobile navigation, logo sizing, and admin-bar offsets |
| `layout/_footer.scss` | Footer decoration, widgets, and social links |
| `pages/_home.scss` | Homepage intro, photo, panels, and pet controls |
| `pages/_journal.scss` | Blog/archive heading and browsing controls |
| `pages/_reading.scss` | Single-post header, article, tags, sharing, and untitled notes |
| `themes/_geek-mode.scss` | Terminal effects and geek-mode overrides |

Keep responsive rules, hover/focus states, and reduced-motion rules alongside
their component. Use one declaration per line, and extend an existing partial
before introducing a new one. Partials are loaded through `app.scss`; they do
not need separate Vite entries or Blade stylesheet links.

## Tailwind and colors

`tailwind.css` remains the separate Tailwind entry point. It owns utility
generation, the typography plugin, light/dark color tokens, and the component
layer override for primary-colored callouts in stored post content. Keep that
override in its Tailwind layer because layer order affects important utilities.

Use the existing CSS custom properties for colors so light and dark modes stay
in sync. Sass combines the modules first; PostCSS then resolves `@apply` and
`@variant` using the `@reference` in `app.scss`. Sass requires `@use` statements
before other rules, so the Tailwind reference follows the module list.

## Build

Run from the theme directory after changing styles:

```sh
npm run production
```

This builds both Sass and Tailwind and updates the asset manifest used by
`blog.test`. Generated files under `public/build` should not be edited by hand.
