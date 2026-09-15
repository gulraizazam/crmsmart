#!/usr/bin/env python3
"""SSH helper for Hostinger. Reads gitignored .deploy/*.env."""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

try:
    import paramiko
except ImportError:
    sys.stderr.write("paramiko is required: pip install paramiko\n")
    sys.exit(1)

ROOT = Path(__file__).resolve().parents[1]
DEFAULT_ENV_FILE = ROOT / ".deploy" / "hostinger.env"


def env_path(name: str | None = None) -> Path:
    if not name:
        return DEFAULT_ENV_FILE
    candidate = Path(name)
    if not candidate.is_absolute():
        candidate = ROOT / ".deploy" / name
    return candidate


def load_env(path: Path | None = None) -> dict[str, str]:
    env_file = path or DEFAULT_ENV_FILE
    if not env_file.exists():
        sys.stderr.write(f"Missing {env_file}\n")
        sys.exit(1)
    data: dict[str, str] = {}
    for line in env_file.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        data[key.strip()] = value.strip()
    return data


def connect(path: Path | None = None):
    env = load_env(path)
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(
        hostname=env["HOSTINGER_HOST"],
        port=int(env["HOSTINGER_PORT"]),
        username=env["HOSTINGER_USER"],
        password=env["HOSTINGER_PASSWORD"],
        timeout=30,
        allow_agent=False,
        look_for_keys=False,
    )
    return client


def run(command: str, timeout: int = 120, path: Path | None = None) -> tuple[int, str, str]:
    client = connect(path)
    try:
        stdin, stdout, stderr = client.exec_command(command, timeout=timeout)
        out = stdout.read().decode("utf-8", errors="replace")
        err = stderr.read().decode("utf-8", errors="replace")
        code = stdout.channel.recv_exit_status()
        return code, out, err
    finally:
        client.close()


def upload(local_path: str, remote_path: str, path: Path | None = None) -> None:
    client = connect(path)
    try:
        sftp = client.open_sftp()
        sftp.put(local_path, remote_path)
        sftp.close()
    finally:
        client.close()


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--timeout", type=int, default=120)
    parser.add_argument("--env", default="", help="Env filename in .deploy/ (default hostinger.env)")
    parser.add_argument("--upload", nargs=2, metavar=("LOCAL", "REMOTE"))
    parser.add_argument("command", nargs="?", default="")
    args = parser.parse_args()
    secrets = env_path(args.env or None)
    if args.upload:
        upload(args.upload[0], args.upload[1], path=secrets)
        print(f"uploaded {args.upload[0]} -> {args.upload[1]}")
        if not args.command:
            return 0
    if not args.command:
        parser.error("command is required unless --upload is used")
    code, out, err = run(args.command, timeout=args.timeout, path=secrets)
    if out:
        sys.stdout.write(out)
    if err:
        sys.stderr.write(err)
    return code


if __name__ == "__main__":
    raise SystemExit(main())
