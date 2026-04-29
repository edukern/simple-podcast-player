const { formatTime, cycleSpeed } = require('../../assets/player.js');

describe('formatTime', () => {
  test('returns --:-- for NaN', () => {
    expect(formatTime(NaN)).toBe('--:--');
  });

  test('returns --:-- for negative', () => {
    expect(formatTime(-1)).toBe('--:--');
  });

  test('returns --:-- for Infinity', () => {
    expect(formatTime(Infinity)).toBe('--:--');
  });

  test('formats seconds under a minute', () => {
    expect(formatTime(5)).toBe('0:05');
  });

  test('formats one full minute', () => {
    expect(formatTime(60)).toBe('1:00');
  });

  test('formats minutes and seconds', () => {
    expect(formatTime(125)).toBe('2:05');
  });

  test('formats hours when >= 3600 seconds', () => {
    expect(formatTime(3661)).toBe('1:01:01');
  });

  test('truncates fractional seconds', () => {
    expect(formatTime(90.9)).toBe('1:30');
  });
});

describe('cycleSpeed', () => {
  test('cycles from 1 to 1.25', () => {
    expect(cycleSpeed(1)).toBe(1.25);
  });

  test('cycles from 1.25 to 1.5', () => {
    expect(cycleSpeed(1.25)).toBe(1.5);
  });

  test('cycles from 1.5 back to 1', () => {
    expect(cycleSpeed(1.5)).toBe(1);
  });
});
