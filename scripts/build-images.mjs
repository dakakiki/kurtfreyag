import fs from "fs";
import path from "path";

const themeRoot = process.cwd();

const srcRoot = path.join(themeRoot, "assets/images");
const outRoot = path.join(themeRoot, "dist/images");

function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
}

function copyAll() {
  if (!fs.existsSync(srcRoot)) {
    console.warn("[images] assets/images not found, skipping");
    return;
  }

  ensureDir(outRoot);

  // Node 16+ supports fs.cpSync
  fs.cpSync(srcRoot, outRoot, {
    recursive: true,
    force: true
  });

  console.log(`[images] synced assets/images → dist/images`);
}

try {
  copyAll();
} catch (err) {
  console.error(err);
  process.exit(1);
}
