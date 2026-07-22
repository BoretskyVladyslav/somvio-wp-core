function readStdin() {
  return new Promise((resolve, reject) => {
    const chunks = [];
    process.stdin.setEncoding('utf8');
    process.stdin.on('data', (chunk) => chunks.push(chunk));
    process.stdin.on('end', () => resolve(chunks.join('')));
    process.stdin.on('error', reject);
  });
}

const BLOCKED = [
  { pattern: 'git push', reason: 'Blocked: git push requires explicit confirmation.' },
  { pattern: 'wp db drop', reason: 'Blocked: wp db drop would destroy the database.' },
  { pattern: 'DROP DATABASE', reason: 'Blocked: DROP DATABASE would destroy the database.' },
  { pattern: 'rm -rf', reason: 'Blocked: rm -rf is a destructive recursive delete.' },
];

async function main() {
  let input;
  try {
    input = JSON.parse(await readStdin() || '{}');
  } catch {
    process.exit(0);
  }

  const command = input.command || '';

  for (const rule of BLOCKED) {
    if (command.includes(rule.pattern)) {
      process.stderr.write(rule.reason + '\n');
      process.exit(2);
    }
  }

  process.exit(0);
}

main().catch(() => process.exit(0));
