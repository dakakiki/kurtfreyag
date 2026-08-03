import chokidar from "chokidar";
import { exec } from "child_process";

let running = false;
let queued = false;

function runMinify() {
  if (running) {
    queued = true;
    return;
  }

  running = true;

  exec("npm run build:css:min", (err, stdout, stderr) => {
    if (stdout) process.stdout.write(stdout);
    if (stderr) process.stderr.write(stderr);
    if (err) console.error(err);

    running = false;

    if (queued) {
      queued = false;
      runMinify();
    }
  });
}

console.log("[watch:css:min] watching dist/css/style.css");

chokidar
  .watch("dist/css/style.css", { ignoreInitial: true })
  .on("change", (file) => {
    console.log(`[watch:css:min] changed → ${file}`);
    runMinify();
  });
