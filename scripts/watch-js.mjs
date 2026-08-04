import chokidar from "chokidar";
import { exec } from "child_process";

const files = [
    "assets/js/custom.js",
    "assets/js/slick.min.js",
    "assets/js/layout_news_slider.min.js",

    "assets/js/gsap.js",
];

let running = false;
let queued = false;

function runBuild() {
  if (running) {
    queued = true;
    return;
  }

  running = true;

  exec("npm run build:js", (err, stdout, stderr) => {
    if (stdout) process.stdout.write(stdout);
    if (stderr) process.stderr.write(stderr);
    if (err) console.error(err);

    running = false;

    if (queued) {
      queued = false;
      runBuild();
    }
  });
}

console.log("[watch:js] watching whitelist:");
files.forEach(f => console.log(" -", f));

chokidar
  .watch(files, { ignoreInitial: true })
  .on("change", (file) => {
    console.log(`[watch:js] changed → ${file}`);
    runBuild();
  });
