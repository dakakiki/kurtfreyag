import fs from "fs";
import path from "path";
import { minify } from "terser";
import { build } from "esbuild";

const isProd = process.env.NODE_ENV === "production";
const themeRoot = process.cwd();

/**
 * JS whitelist
 * Source → Output
 */
const entries = [
  { in: "assets/js/custom.js", out: "dist/js/custom.min.js", bundle: false },
  { in: "assets/js/slick.min.js", out: "dist/js/slick.min.js", bundle: false },
  { in: "assets/js/layout_news_slider.js", out: "dist/js/layout_news_slider.min.js", bundle: false },
  { in: "assets/js/layout_news_archive.js", out: "dist/js/layout_news_archive.min.js", bundle: false },
  { in: "assets/js/layout_areas.js", out: "dist/js/layout_areas.min.js", bundle: false },
  { in: "assets/js/layout_iwiaat.js", out: "dist/js/layout_iwiaat.min.js", bundle: false },

  { in: "assets/js/gsap.js", out: "dist/js/gsap.min.js", bundle: true },
];

function ensureDir(filePath) {
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
}

async function buildWithTerser(inputRel, outputRel) {
  const inputPath = path.join(themeRoot, inputRel);
  const outputPath = path.join(themeRoot, outputRel);

  if (!fs.existsSync(inputPath)) {
    console.warn(`[skip] ${inputRel} not found`);
    return;
  }

  const code = fs.readFileSync(inputPath, "utf8");

  const result = await minify(code, {
    compress: isProd
      ? {
          passes: 2,
          drop_console: ["log", "info"],
          drop_debugger: true,
          dead_code: true,
        }
      : {
          passes: 1,
          dead_code: true,
        },
    mangle: {
      safari10: true,
    },
    format: {
      comments: false,
    },
  });

  if (!result.code) {
    throw new Error(`Minification failed for ${inputRel}`);
  }

  ensureDir(outputPath);
  fs.writeFileSync(outputPath, result.code, "utf8");

  console.log(`[js:${isProd ? "prod" : "dev"}] ${inputRel} → ${outputRel}`);
}

async function buildWithEsbuild(inputRel, outputRel) {
  const inputPath = path.join(themeRoot, inputRel);
  const outputPath = path.join(themeRoot, outputRel);

  if (!fs.existsSync(inputPath)) {
    console.warn(`[skip] ${inputRel} not found`);
    return;
  }

  ensureDir(outputPath);

  await build({
    entryPoints: [inputPath],
    outfile: outputPath,
    bundle: true,
    minify: true,
    sourcemap: !isProd,
    target: ["es2018"],
    format: "iife",
    platform: "browser",
    logLevel: "silent",
  });

  console.log(`[js:${isProd ? "prod" : "dev"}][bundle] ${inputRel} → ${outputRel}`);
}

async function run() {
    for (const entry of entries) {
        if (entry.bundle) {
            await buildWithEsbuild(entry.in, entry.out);
        } else {
            await buildWithTerser(entry.in, entry.out);
        }
    }
}

run().catch((err) => {
  console.error(err);
  process.exit(1);
});