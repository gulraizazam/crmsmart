#!/usr/bin/env python3
"""SSH helper for Hostinger. Reads gitignored .deploy/hostinger.env."""

from __future__ import annotations

import argparse
import os
import sys
from pathlib import Path

try:
    import paramiko
except ImportError:
    sys.stderr.write("paramiko is required: pip install paramiko\n")
    sys.exit(1)

ROOT = Path(__file__).resolve().parents[1]
ENV_FILE = ROOT / ".deploy" / "hostinger.env"


def load_env() -> dict[str, str]:
    if not ENV_FILE.exists():
        sys.stderr.write(f"Missing {ENV_FILE}\n")
        sys.exit(1)
    data: dict[str, str] = {}
    for line in ENV_FILE.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        data[key.strip()] = value.strip()
    return data


def connect():
    env = load_env()
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


def run(command: str, timeout: int = 120) -> tuple[int, str, str]:
    client = connect()
    try:
        stdin, stdout, stderr = client.exec_command(command, timeout=timeout)
        out = stdout.read().decode("utf-8", errors="replace")
        err = stderr.read().decode("utf-8", errors="replace")
        code = stdout.channel.recv_exit_status()
        return code, out, err
    finally:
        client.close()


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("command")
    parser.add_argument("--timeout", type=int, default=120)
    args = parser.parse_args()
    code, out, err = run(args.command, timeout=args.timeout)
    if out:
        sys.stdout.write(out)
    if err:
        sys.stderr.write(err)
    return code


if __name__ == "__main__":
    raise SystemExit(main())
