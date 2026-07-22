const { spawnSync, execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

function readStdin() {
  return new Promise((resolve, reject) => {
    const chunks = [];
    process.stdin.setEncoding('utf8');
    process.stdin.on('data', (chunk) => chunks.push(chunk));
    process.stdin.on('end', () => resolve(chunks.join('')));
    process.stdin.on('error', reject);
  });
}

function commandExists(cmd) {
  try {
    if (process.platform === 'win32') {
      execSync(`where ${cmd}`, { stdio: 'ignore' });
    } else {
      execSync(`command -v ${cmd}`, { stdio: 'ignore', shell: true });
    }
    return true;
  } catch {
    return false;
  }
}

function fileExists(p) {
  try {
    fs.accessSync(p);
    return true;
  } catch {
    return false;
  }
}

function run(cmd, args) {
  const result = spawnSync(cmd, args, {
    encoding: 'utf8',
    shell: process.platform === 'win32',
    stdio: ['ignore', 'pipe', 'pipe'],
  });
  if (result.stdout) process.stdout.write(result.stdout);
  if (result.stderr) process.stderr.write(result.stderr);
}

function hasPhpcs() {
  if (commandExists('phpcs')) return true;
  const vendorBin =
    process.platform === 'win32'
      ? path.join(process.cwd(), 'vendor', 'bin', 'phpcs.bat')
      : path.join(process.cwd(), 'vendor', 'bin', 'phpcs');
  return fileExists(vendorBin);
}

function hasEslint() {
  if (commandExists('eslint')) return true;
  const local =
    process.platform === 'win32'
      ? path.join(process.cwd(), 'node_modules', '.bin', 'eslint.cmd')
      : path.join(process.cwd(), 'node_modules', '.bin', 'eslint');
  return fileExists(local);
}

async function main() {
  let input;
  try {
    input = JSON.parse((await readStdin()) || '{}');
  } catch {
    process.exit(0);
  }

  const filePath = input.file_path || '';
  if (!filePath) {
    process.exit(0);
  }

  const ext = path.extname(filePath).toLowerCase();

  if (ext === '.php') {
    if (!hasPhpcs()) {
      process.exit(0);
    }
    if (commandExists('phpcs')) {
      run('phpcs', [filePath]);
    } else {
      const bin = path.join(process.cwd(), 'vendor', 'bin', 'phpcs');
      run(bin, [filePath]);
    }
    process.exit(0);
  }

  if (ext === '.ts' || ext === '.tsx') {
    if (!hasEslint()) {
      process.exit(0);
    }
    run('npx', ['eslint', '--fix', filePath]);
    process.exit(0);
  }

  process.exit(0);
}

main().catch(() => process.exit(0));
