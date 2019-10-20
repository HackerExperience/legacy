# Hacker Experience Legacy

This is the source-code for Legacy, the first version of Hacker Experience I built from 2012-2014 and published on 2014. I made a promise I'd release it and here it is.

Legacy reached the 1-million registered players milestone 5 years after it was released, and soon after I decided to shut it down, since I no longer could maintain it. [Context about why I decided to shut it down](https://medium.com/@renatomassaro/updates-on-hacker-experience-legacy-eb5a9e0aee33).

If you were one of these players, I hope you had a good time with Legacy! I also hope that, by releasing its code, someone else can maintain a server on which you can keep playing it.

## Disclaimer

Legacy was my first programming project. I learned to code by building Legacy. As such, its codebase is *terrible*. It has no tests, no architecture, virtually no documentation and no warranties that it will work as expected, or in a secure manner. In fact, it's more likely it will *not* work as expected. You've been warned.

## Documentation

The closest I have to a documentation can be found at the `info/` folder. Keep in mind it might be outdated, but it's better than nothing.

## Redactions and comments

This is the exact same code that powered Legacy, except for a few hard-coded passwords and API keys that were replaced with the `REDACTED` strings. I also removed images.

I did a quick code-review and added some comments that might help you understand the code. I also added translations to comments in Portuguese (except for the ones I had no idea what I wrote originally). All my comments are prefixed with `2019: `.

## Setup

Legacy uses PHP 5 and MySQL, alongside some Python 2 scripts running on cron jobs. You can build the database schema from the `game.sql` file. A model of the cron file can be found at `crontab`.

If you have questions and/or need help setting it up or understanding its code, please open an issue. Your question may be someone else's, so do not hesitate asking it. I can't guarantee fast responses, but I'll try to help you as soon as possible.

Make sure to scan the code for the words `REDACTED` and `2019`. `REDACTED` will contain placeholders for API keys and passwords that you might want to change. `2019` may contain useful comments.

## License

Legacy is published under the MIT license, as described in the `LICENSE` file. You can run your own private game server and display ads or charge money however you like, with no ties to me and/or Neoart Labs.

The MIT license does not give you the right to use the brand Hacker Experience commercially, which is a registered trademark. In other words, please do not name your game server "Hacker Experience Continued" or anything like that. As always, [fair usage](https://support.google.com/legal/answer/4558992?hl=en), like mentioning it's based on Legacy, is OK.

## Images

I did not include with the source code most of the images and icons used in the game. You should get them yourself and make sure you understand the attribution requirements.

Most of the game icons were from the amazing [famfamfam](http://www.famfamfam.com/lab/icons/silk/) iconset. You can use it as long as you credit it.

## Credits

The original Legacy had a credits section at the bottom of the page, which is included in the source code. Please make sure to update it accordingly.

Please make sure to remove any comment or phrase that could be understood as an endorsement from myself or Neoart Labs.

## Affiliation disclaimer

All game servers that are based off of Legacy's codebase are in no way affiliated, endorsed or recommended by myself or Neoart Labs.

## Data disclaimer

I did not and I will not release the database contents from Legacy (including registered players' emails and usernames). In fact the last backup I had has been destroyed for good.

## Limitation of liability

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
