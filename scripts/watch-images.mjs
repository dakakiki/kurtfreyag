import chokidar from "chokidar";
import fs from "fs";
import path from "path";

const themeRoot = process.cwd();

const srcRoot = path.join(themeRoot, "assets/images");
const outRoot = path.join(themeRoot, "dist/images");

function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
}

function toOutPath(srcPath) {
  const rel = path.relative(srcRoot, srcPath);
  return path.join(outRoot, rel);
}

function copyFile(srcPath) {
  const outPath = toOutPath(srcPath);
  ensureDir(path.dirname(outPath));
  fs.copyFileSync(srcPath, outPath);
  console.log(`[watch:images] copied → ${path.relative(themeRoot, outPath)}`);
}

function removeFile(srcPath) {
  const outPath = toOutPath(srcPath);
  if (fs.existsSync(outPath)) {
    fs.unlinkSync(outPath);
    console.log(`[watch:images] removed → ${path.relative(themeRoot, outPath)}`);
  }
}

function ensureFolder(srcDir) {
  const outDir = toOutPath(srcDir);
  ensureDir(outDir);
  console.log(`[watch:images] dir created → ${path.relative(themeRoot, outDir)}`);
}

function removeFolder(srcDir) {
  const outDir = toOutPath(srcDir);
  if (fs.existsSync(outDir)) {
    fs.rmSync(outDir, { recursive: true, force: true });
    console.log(`[watch:images] dir removed → ${path.relative(themeRoot, outDir)}`);
  }
}

// Initial setup: make sure dist/images exists and do an initial sync if src exists
if (fs.existsSync(srcRoot)) {
  ensureDir(outRoot);
  try {
    fs.cpSync(srcRoot, outRoot, { recursive: true, force: true });
    console.log("[watch:images] initial sync complete");
  } catch (e) {
    console.error(e);
  }
} else {
  console.log("[watch:images] assets/images not found (will still watch if it appears)");
  ensureDir(outRoot);
}

console.log("[watch:images] watching: assets/images/**/*");

chokidar
  .watch(srcRoot, {
    ignoreInitial: true,
    awaitWriteFinish: {
      stabilityThreshold: 150,
      pollInterval: 50
    }
  })
  .on("add", (p) => copyFile(p))
  .on("change", (p) => copyFile(p))
  .on("unlink", (p) => removeFile(p))
  .on("addDir", (p) => ensureFolder(p))
  .on("unlinkDir", (p) => removeFolder(p))
  .on("error", (err) => console.error("[watch:images] error:", err));
