#!/usr/bin/env bash
set -euo pipefail

# Lấy root của repo (thư mục chứa docs/, public/, v.v.)
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DOCS_DIR="$ROOT_DIR/docs"
OUT_DIR="$ROOT_DIR/public"

echo "[build-docs] ROOT_DIR = $ROOT_DIR"
echo "[build-docs] DOCS_DIR = $DOCS_DIR"
echo "[build-docs] OUT_DIR  = $OUT_DIR"

# Xoá build cũ
rm -rf "$OUT_DIR"
mkdir -p "$OUT_DIR"

# 1) Build portal index nếu có
PORTAL_INDEX="$DOCS_DIR/index.adoc"
if [[ -f "$PORTAL_INDEX" ]]; then
  echo "[build-docs] Building portal index: $PORTAL_INDEX"
  asciidoctor -D "$OUT_DIR" "$PORTAL_INDEX"
else
  echo "[build-docs] No docs/index.adoc found, skip portal index."
fi

# 2) Build từng "phòng ban" = mỗi thư mục con trong docs/
echo "[build-docs] Scanning sections under $DOCS_DIR ..."

for SECTION_DIR in "$DOCS_DIR"/*/; do
  # Nếu không có thư mục con nào, for sẽ lặp nguyên string
  [[ -d "$SECTION_DIR" ]] || continue

  SECTION_NAME="$(basename "$SECTION_DIR")"

  # Bỏ qua một số thư mục đặc biệt nếu cần
  case "$SECTION_NAME" in
    assets|_templates|.git) 
      echo "[build-docs] Skip special dir: $SECTION_NAME"
      continue
      ;;
  esac

  SECTION_INDEX="$SECTION_DIR/index.adoc"
  if [[ ! -f "$SECTION_INDEX" ]]; then
    echo "[build-docs] WARN: No index.adoc in section '$SECTION_NAME', skip."
    continue
  fi

  OUT_SECTION_DIR="$OUT_DIR/$SECTION_NAME"
  mkdir -p "$OUT_SECTION_DIR"

  echo "[build-docs] Building section '$SECTION_NAME' from $SECTION_INDEX -> $OUT_SECTION_DIR"

  # Build index.adoc của section
  asciidoctor -R "$SECTION_DIR" -D "$OUT_SECTION_DIR" "$SECTION_INDEX"

  # Copy images nếu có
  if [[ -d "$SECTION_DIR/images" ]]; then
    echo "[build-docs] Copy images for '$SECTION_NAME'"
    rsync -a "$SECTION_DIR/images/" "$OUT_SECTION_DIR/images/"
  fi

  # (Optional) Copy static nếu sau này bạn dùng (css/js riêng cho từng phòng ban)
  if [[ -d "$SECTION_DIR/static" ]]; then
    echo "[build-docs] Copy static assets for '$SECTION_NAME'"
    rsync -a "$SECTION_DIR/static/" "$OUT_SECTION_DIR/static/"
  fi
done

echo "[build-docs] Done. Output at: $OUT_DIR"